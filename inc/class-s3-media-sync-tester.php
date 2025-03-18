<?php

/**
 * S3 Media Sync Tester Class
 * 
 * Handles testing of S3 connection and operations.
 */
class S3_Media_Sync_Tester {
    /**
     * Plugin settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Debug information array
     *
     * @var array
     */
    protected $debug_info = [];

    /**
     * S3 client instance
     *
     * @var \Aws\S3\S3Client
     */
    protected $s3_client;

    /**
     * Constructor
     *
     * @param array $settings Plugin settings
     */
    public function __construct(array $settings) {
        $this->settings = $settings;
    }

    /**
     * Run all the S3 tests
     * 
     * @return void
     */
    public function run_tests() {
        try {
            // Step 1: Test credential validation
            $this->test_credentials();
            
            // Step 2: Test bucket access
            $this->test_bucket_access();
            
            // Step 3: Test basic operations
            $this->test_operations();
            
            // If we made it here, everything was successful
            $this->add_success_message();
            
        } catch (\Aws\Exception\CredentialsException $e) {
            $this->handle_credential_exception($e);
        } catch (\Exception $e) {
            $this->handle_general_exception($e);
        }
    }

    /**
     * Test credential validation
     * 
     * @throws \Aws\Exception\CredentialsException
     * @throws \Exception
     * @return void
     */
    protected function test_credentials() {
        $this->debug_info[] = "Step 1: Creating S3 client with credentials";
        $this->debug_info[] = "- Using region from settings: " . $this->settings['region'];
        
        try {
            // Try to create credentials separately to catch invalid keys early
            $credentials = new \Aws\Credentials\Credentials(
                $this->settings['key'],
                $this->settings['secret']
            );
            
            // Test the credentials with a small STS call
            $sts_client = new \Aws\Sts\StsClient([
                'region' => $this->settings['region'],
                'version' => 'latest',
                'credentials' => $credentials
            ]);
            
            // This will fail immediately with invalid credentials
            try {
                $identity = $sts_client->getCallerIdentity();
                $this->debug_info[] = "- Credentials validated successfully";
                $this->debug_info[] = "- AWS Account: " . $identity['Account'];
                $this->debug_info[] = "- IAM User/Role ARN: " . $identity['Arn'];
            } catch (\Exception $sts_e) {
                // Check if this is actually a credentials error
                $error_message = wp_strip_all_tags($sts_e->getMessage());
                $this->debug_info[] = "- Credential check failed: " . $error_message;
                
                if (strpos($error_message, 'InvalidClientTokenId') !== false || 
                    strpos($error_message, 'SignatureDoesNotMatch') !== false || 
                    strpos($error_message, 'InvalidAccessKeyId') !== false) {
                    // This is definitely a credentials issue
                    throw new \Aws\Exception\CredentialsException(
                        'AWS credential validation failed: ' . $error_message
                    );
                }
                // Otherwise continue - it might be a permissions issue
            }
            
            // Now create the actual S3 client
            $factory = new S3_Media_Sync_Client_Factory();
            $this->s3_client = $factory->create($this->settings);
            $this->debug_info[] = "- S3 client created successfully";
            $this->debug_info[] = "- Actual client region: " . $this->s3_client->getRegion();
            
        } catch (\Aws\Exception\CredentialsException $ce) {
            // Catch credential exceptions immediately to avoid running further tests
            $this->debug_info[] = "- Credential error detected in Step 1";
            throw $ce;
        }
    }

    /**
     * Test bucket access
     * 
     * @throws \Exception
     * @return array [bucket_name, prefix]
     */
    protected function test_bucket_access() {
        // Extract the actual bucket name if there's a path prefix
        $bucket_parts = explode('/', $this->settings['bucket']);
        $bucket_name = $bucket_parts[0];
        $prefix = count($bucket_parts) > 1 ? implode('/', array_slice($bucket_parts, 1)) : '';
        $this->debug_info[] = "- Using bucket: $bucket_name" . ($prefix ? " with prefix: $prefix" : "");
        
        // Test 2: Check if bucket exists
        $this->debug_info[] = "Step 2: Checking if bucket exists and is accessible";
        try {
            $bucket_exists = $this->s3_client->doesBucketExist($this->settings['bucket']);
            if ($bucket_exists) {
                $this->debug_info[] = "- Bucket '{$this->settings['bucket']}' exists and is accessible";
            } else {
                $bucket_error = true;
                $this->debug_info[] = "- Bucket '{$this->settings['bucket']}' does not exist or is not accessible";
                
                // For bucket existence failures, try to determine if it's a region or name issue
                // First check if we can resolve the regional endpoint (region issue check)
                try {
                    // Many AWS regions can be resolved through s3.{region}.amazonaws.com
                    // but some regions like us-east-1 use s3.amazonaws.com
                    $domain = "{$this->settings['region']}.amazonaws.com";
                    if ($this->settings['region'] === 'us-east-1') {
                        $domain = "s3.amazonaws.com"; // Special case for us-east-1
                    }
                    
                    $this->debug_info[] = "- Testing DNS resolution for region endpoint: $domain";
                    $ip = gethostbyname($domain);
                    
                    // If we get here with a resolved IP that's different from the domain name,
                    // the region exists but the bucket name is likely wrong
                    if ($ip !== $domain) {
                        $this->debug_info[] = "- Region endpoint resolves correctly to $ip";
                        
                        // Now check if the bucket exists in a different region
                        try {
                            // First verify the region by trying a small operation
                            // If this fails with NoSuchBucket, then the bucket name is wrong
                            // If it fails with a region redirect, then we know the region is wrong
                            
                            $this->s3_client->headBucket([
                                'Bucket' => $this->settings['bucket']
                            ]);
                            
                            // If we get here, the bucket exists and is accessible
                            $this->debug_info[] = "- Bucket actually does exist! Might be a permission issue.";
                            throw new \Exception("OPERATION_ERROR: The bucket exists but there might be permission issues accessing it.");
                            
                        } catch (\Aws\S3\Exception\S3Exception $head_e) {
                            $error_code = $head_e->getAwsErrorCode();
                            $this->debug_info[] = "- Detailed bucket check error: $error_code - " . $head_e->getMessage();
                            
                            if (strpos($head_e->getMessage(), "PermanentRedirect") !== false) {
                                // Bucket exists but in a different region
                                $this->debug_info[] = "- Bucket exists but in a different region";
                                throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' is incorrect for this bucket. The bucket exists but is in a different region.");
                            } else if ($error_code === 'NoSuchBucket' || strpos($head_e->getMessage(), "NoSuchBucket") !== false) {
                                // Bucket truly doesn't exist
                                $this->debug_info[] = "- Bucket confirmed not to exist (NoSuchBucket)";
                                throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
                            } else {
                                // This could be a permission issue
                                $this->debug_info[] = "- Bucket existence check failed with access error";
                                throw new \Exception("OPERATION_ERROR: Unable to access the bucket. Please check your permissions.");
                            }
                        }
                        
                    } else {
                        // If we can't resolve the domain, it's likely a region issue
                        $this->debug_info[] = "- Failed to resolve region endpoint domain. Region may be invalid.";
                        throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' appears to be invalid. Please verify your region setting is correct.");
                    }
                } catch (\Exception $dns_e) {
                    // If DNS check itself fails, fall back to original exception
                    $this->debug_info[] = "- DNS resolution check failed: " . $dns_e->getMessage();
                    
                    // Try a known working region to better determine if it's a region or bucket issue
                    try {
                        $this->debug_info[] = "- Attempting to check bucket with fallback region (us-east-1)";
                        $alt_client = new \Aws\S3\S3Client([
                            'region' => 'us-east-1',
                            'version' => 'latest',
                            'credentials' => new \Aws\Credentials\Credentials(
                                $this->settings['key'],
                                $this->settings['secret']
                            )
                        ]);
                        
                        // Try to access the bucket with the alternate region
                        $bucket_exists = $alt_client->doesBucketExist($this->settings['bucket']);
                        
                        if ($bucket_exists) {
                            // If the bucket exists with a different region, then the region was wrong
                            $this->debug_info[] = "- Bucket found using alternate region. Original region is likely incorrect.";
                            throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' is incorrect for this bucket. The bucket exists but is in a different region.");
                        } else {
                            // If the bucket doesn't exist with the alternate region either, then the bucket name is likely wrong
                            $this->debug_info[] = "- Bucket not found using alternate region. Bucket name is likely incorrect.";
                            throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
                        }
                    } catch (\Exception $alt_e) {
                        // Preserve any specific error messages from inner exceptions
                        if (strpos($alt_e->getMessage(), "REGION_ERROR:") === 0 ||
                            strpos($alt_e->getMessage(), "BUCKET_NAME_ERROR:") === 0 ||
                            strpos($alt_e->getMessage(), "OPERATION_ERROR:") === 0) {
                            throw $alt_e;
                        }
                        
                        // If we get here with no specific error type, default to bucket name error with a complete message
                        $this->debug_info[] = "- Alternate region check failed with error: " . $alt_e->getMessage();
                        throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
                    }
                }
            }
        } catch (\Exception $e) {
            $bucket_error = true;
            $error_message = wp_strip_all_tags($e->getMessage());
            $this->debug_info[] = "- Bucket check error: $error_message";
            
            // Only do additional error detection if not already classified
            if (strpos($error_message, "REGION_ERROR:") === false && 
                strpos($error_message, "BUCKET_NAME_ERROR:") === false &&
                strpos($error_message, "BUCKET_ERROR:") === false) {
                
                // Check for specific error patterns to provide better messages
                $complete_message = $e->getMessage();
                
                // Region errors have very specific signatures
                if (strpos($complete_message, "cURL error 6: Could not resolve host") !== false) {
                    throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' appears to be invalid. Please verify your region setting is correct.");
                } 
                // NoSuchBucket is a clear indicator of wrong bucket name with correct region
                else if (strpos($complete_message, "404 Not Found") !== false || 
                         strpos($complete_message, "NoSuchBucket") !== false) {
                    throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
                } 
                // Other bucket errors
                else {
                    throw new \Exception("BUCKET_ERROR: The bucket '{$this->settings['bucket']}' could not be accessed. Please verify your bucket settings and permissions.");
                }
            } else {
                // Re-throw already classified errors
                throw $e;
            }
        }
        
        return [$bucket_name, $prefix];
    }

    /**
     * Test basic S3 operations
     * 
     * @throws \Exception
     * @return void
     */
    protected function test_operations() {
        try {
            $this->debug_info[] = "Step 3: Testing basic S3 operations";

            // Extract bucket name for operations
            $bucket_parts = explode('/', $this->settings['bucket']);
            $bucket_name = $bucket_parts[0];
            
            // Check if ACLs are supported - if use_acl is set to false, we'll skip the ACL check
            $use_acl = isset($this->settings['use_acl']) ? $this->settings['use_acl'] : true;
            
            if ($use_acl) {
                // Try to check if the bucket allows ACLs
                try {
                    $factory = new S3_Media_Sync_Client_Factory();
                    $acl_allowed = $factory->does_bucket_allow_acl($this->s3_client, $bucket_name);
                    
                    if (!$acl_allowed) {
                        $this->debug_info[] = "- Detected bucket has ACLs disabled. Will operate without ACLs.";
                        $use_acl = false;
                    } else {
                        $this->debug_info[] = "- Bucket allows ACLs, proceeding with ACL settings.";
                    }
                } catch (\Exception $e) {
                    // If we can't determine, assume it's allowed
                    $this->debug_info[] = "- Could not verify ACL support status: " . $e->getMessage();
                }
            } else {
                $this->debug_info[] = "- ACLs are disabled in settings, will operate without ACLs.";
            }
            
            // Test operation
            $test_key = 'wp-content/uploads/s3-media-sync-test-' . time() . '.txt';
            $this->debug_info[] = "- Attempting to put a test object: $test_key";
            
            $params = [
                'Bucket' => $bucket_name,
                'Key'    => $test_key,
                'Body'   => 'This is a test file from S3 Media Sync',
            ];
            
            // Only set ACL if the bucket supports it and ACLs are enabled in settings
            if ($use_acl) {
                $params['ACL'] = isset($this->settings['object_acl']) ? $this->settings['object_acl'] : 'public-read';
                $this->debug_info[] = "- Setting object ACL: " . $params['ACL'];
            }
            
            try {
                $this->s3_client->putObject($params);
                $this->debug_info[] = "- Successfully uploaded test object";
            } catch (\Aws\S3\Exception\S3Exception $e) {
                // Check if the error is related to ACLs
                if (strpos($e->getMessage(), 'AccessControlListNotSupported') !== false) {
                    $this->debug_info[] = "- Received AccessControlListNotSupported error. Retrying without ACL.";
                    
                    // Remove ACL and try again
                    unset($params['ACL']);
                    $this->s3_client->putObject($params);
                    $this->debug_info[] = "- Successfully uploaded test object without ACL";
                    
                    // Update settings to disable ACLs
                    $this->debug_info[] = "- Recommending disabling ACLs in settings";
                    $use_acl = false;
                } else {
                    // For other errors, rethrow
                    throw $e;
                }
            }
            
            $this->debug_info[] = "- Attempting to get the test object";
            $this->s3_client->getObject([
                'Bucket' => $bucket_name,
                'Key'    => $test_key,
            ]);
            $this->debug_info[] = "- Successfully downloaded test object";
            
            $this->debug_info[] = "- Attempting to delete the test object";
            $this->s3_client->deleteObject([
                'Bucket' => $bucket_name,
                'Key'    => $test_key,
            ]);
            $this->debug_info[] = "- Successfully deleted test object";
            
            $this->debug_info[] = "All tests completed successfully!";
            
            // If we detected ACLs aren't supported, suggest updating settings
            if (!$use_acl && isset($this->settings['use_acl']) && $this->settings['use_acl']) {
                $this->debug_info[] = "- Note: Your bucket has ACLs disabled. Consider disabling the 'Use ACLs' setting.";
            }
            
        } catch (\Exception $e) {
            // Check if this is a credential error
            $error_message = wp_strip_all_tags($e->getMessage());
            
            // Handle AccessControlListNotSupported error as a bucket configuration issue
            if (strpos($error_message, 'AccessControlListNotSupported') !== false) {
                throw new \Exception("BUCKET_ERROR: This bucket has ACLs disabled (Object Ownership set to 'Bucket owner enforced'). Please disable the 'Use ACLs' setting in your S3 Media Sync configuration.");
            }
            
            if (strpos($error_message, 'InvalidClientTokenId') !== false || 
                strpos($error_message, 'SignatureDoesNotMatch') !== false || 
                strpos($error_message, 'InvalidAccessKeyId') !== false) {
                // Rethrow as a credentials exception
                $filtered_debug_info = array_filter($this->debug_info, function($line) {
                    return strpos($line, "Credential error detected") === false;
                });
                $this->debug_info = $filtered_debug_info;
                $this->debug_info[] = "- Credential error detected in operation test";
                throw new \Exception("CREDENTIAL_ERROR: Your AWS access credentials are invalid. Please check your Access Key and Secret Key.");
            }
            
            if ($e instanceof \Aws\S3\Exception\S3Exception) {
                $error_code = $e->getAwsErrorCode() ?: $e->getStatusCode();
                $error_message = $e->getAwsErrorMessage() ?: $e->getMessage();
                
                // Sanitize the error message to avoid HTML issues
                $error_message = wp_strip_all_tags($error_message);
                $this->debug_info[] = "- Operation failed: $error_code - $error_message";
                
                // Complete message for detailed pattern matching
                $complete_message = $e->getMessage();
                
                // For certain errors, we want to throw specific error types
                if ($error_code === 'NoSuchBucket' || strpos($complete_message, "NoSuchBucket") !== false) {
                    throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
                } else if ($error_code == '403' || $error_code == 403 || $error_code == 'Forbidden' || $error_code == 'AccessDenied') {
                    throw new \Exception("OPERATION_ERROR: Access denied. The AWS user does not have sufficient permissions for S3 operations.");
                } else if (strpos($complete_message, "cURL error 6: Could not resolve host") !== false) {
                    throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' appears to be invalid. Please verify your region setting is correct.");
                } else {
                    throw new \Exception("OPERATION_ERROR: Error testing S3 access: $error_code. Please check your bucket configuration and permissions.");
                }
            } else {
                // Generic error handling
                $complete_message = $e->getMessage();
                
                // Check for more specific error patterns
                if (strpos($complete_message, "cURL error 6: Could not resolve host") !== false) {
                    throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' appears to be invalid. Please verify your region setting is correct.");
                } else if (strpos($complete_message, "404 Not Found") !== false || 
                           strpos($complete_message, "NoSuchBucket") !== false) {
                    throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
                } else {
                    throw new \Exception("OPERATION_ERROR: Error testing S3 access. Please verify your bucket settings and permissions.");
                }
                
                $this->debug_info[] = "- Operation error: " . get_class($e) . " - " . wp_strip_all_tags($e->getMessage());
            }
        }
    }

    /**
     * Add success message after all tests pass
     */
    protected function add_success_message() {
        // Add success message
        $technical_details = sprintf(
            '<div class="s3-media-sync-technical-details">
                <p><a href="#" onclick="jQuery(\'#success-details-%s\').toggle(); return false;">%s</a></p>
                <div id="success-details-%s" style="display: none;">
                    <p><strong>%s</strong></p>
                    <pre>%s</pre>
                </div>
            </div>',
            substr(md5(time()), 0, 6),
            __('Show Details', 's3-media-sync'),
            substr(md5(time()), 0, 6),
            __('Test Steps:', 's3-media-sync'),
            implode("\n", $this->debug_info)
        );
        
        add_settings_error(
            's3_media_sync_settings',
            's3-media-sync-test-success',
            __('Success! Your AWS credentials have the required permissions.', 's3-media-sync') . $technical_details,
            'success'
        );
    }

    /**
     * Handle credential exceptions
     * 
     * @param \Aws\Exception\CredentialsException $e
     * @return void
     */
    protected function handle_credential_exception($e) {
        // Sanitize the error message to avoid HTML issues
        $error_msg = wp_strip_all_tags($e->getMessage());
        
        // Clean up debug info to avoid duplicate error messages
        // If we already have a credential error detected message, we don't need to add the full error again
        $contains_credential_error = false;
        foreach ($this->debug_info as $line) {
            if (strpos($line, "Credential error detected") !== false) {
                $contains_credential_error = true;
                break;
            }
        }
        
        if (!$contains_credential_error) {
            $this->debug_info[] = "- Credential error: " . $error_msg;
        }
        
        // Filter out all lines containing "Credential error detected" for cleaner output
        $filtered_debug_info = array_filter($this->debug_info, function($line) {
            return strpos($line, "Credential error detected") === false;
        });
        
        // Create a simpler, more user-friendly message
        $simple_message = __('Your AWS credentials appear to be invalid. Please verify your Access Key ID and Secret Access Key are correct.', 's3-media-sync');
        $error_type = "credential";
        
        // Create unique ID for the error details element
        $error_id = 'aws-error-credential-' . substr(md5(time()), 0, 6);
        
        // Create collapsible technical details with clean debug info
        $technical_details = sprintf(
            '<div class="s3-media-sync-technical-details">
                <p><a href="#" onclick="jQuery(\'#%s\').toggle(); return false;">%s</a></p>
                <div id="%s" style="display: none;">
                    <p><strong>%s</strong></p>
                    <pre>%s</pre>
                </div>
            </div>',
            $error_id,
            __('Show Technical Details', 's3-media-sync'),
            $error_id,
            __('Debug Steps:', 's3-media-sync'),
            implode("\n", $filtered_debug_info)
        );
        
        add_settings_error(
            's3_media_sync_settings',
            's3-media-sync-test-error',
            $simple_message . $technical_details,
            'error'
        );
    }

    /**
     * Handle general exceptions
     * 
     * @param \Exception $e
     * @return void
     */
    protected function handle_general_exception($e) {
        $error_message = $e->getMessage();
        $error_type = "";
        
        // Extract error type if it exists
        if (strpos($error_message, "REGION_ERROR:") === 0) {
            $error_type = "region";
            $error_message = str_replace("REGION_ERROR: ", "", $error_message);
            $simple_message = $error_message;
        } else if (strpos($error_message, "BUCKET_NAME_ERROR:") === 0) {
            $error_type = "bucket_name"; // Use a distinct type for bucket name errors
            $error_message = str_replace("BUCKET_NAME_ERROR: ", "", $error_message);
            $simple_message = $error_message;
        } else if (strpos($error_message, "BUCKET_ERROR:") === 0) {
            $error_type = "bucket"; // Generic bucket error
            $error_message = str_replace("BUCKET_ERROR: ", "", $error_message);
            $simple_message = $error_message;
        } else if (strpos($error_message, "CREDENTIAL_ERROR:") === 0) {
            $error_type = "credential";
            $error_message = str_replace("CREDENTIAL_ERROR: ", "", $error_message);
            $simple_message = $error_message;
        } else if (strpos($error_message, "OPERATION_ERROR:") === 0) {
            $error_type = "operation";
            $error_message = str_replace("OPERATION_ERROR: ", "", $error_message);
            $simple_message = $error_message;
        } else {
            // Generic error handler - more aggressive pattern detection
            $complete_message = $error_message;
            if (strpos($complete_message, "cURL error 6: Could not resolve host") !== false) {
                $error_type = "region"; // Explicitly set error type
                $simple_message = sprintf(
                    __('The region "%s" appears to be invalid. Please verify your region setting is correct.', 's3-media-sync'),
                    $this->settings['region']
                );
            } else if (strpos($complete_message, "404 Not Found") !== false || 
                       strpos($complete_message, "NoSuchBucket") !== false) {
                $error_type = "bucket_name"; // Explicitly set error type
                $simple_message = sprintf(
                    __('The bucket "%s" does not exist. Please verify your bucket name is correct.', 's3-media-sync'),
                    $this->settings['bucket']
                );
            } else {
                $error_type = "generic"; // Mark as truly generic
                $simple_message = __('Error testing S3 access. Please verify your settings.', 's3-media-sync');
            }
        }
        
        // Filter out all lines containing "Credential error detected" for cleaner output
        $filtered_debug_info = array_filter($this->debug_info, function($line) {
            return strpos($line, "Credential error detected") === false;
        });
        
        // Ensure simple_message is always set, even if somehow missed in error extraction
        if (empty($simple_message)) {
            // Detect error type from debug info as a fallback
            $debug_text = implode("\n", $filtered_debug_info);
            
            if (strpos($debug_text, "REGION_ERROR:") !== false || 
                strpos($debug_text, "Failed to resolve region endpoint") !== false) {
                $simple_message = sprintf(
                    __('The region "%s" appears to be invalid. Please verify your region setting is correct.', 's3-media-sync'),
                    $this->settings['region']
                );
                $error_type = "region";
            } 
            else if (strpos($debug_text, "BUCKET_NAME_ERROR:") !== false || 
                     strpos($debug_text, "Bucket not found") !== false || 
                     strpos($debug_text, "NoSuchBucket") !== false) {
                $simple_message = sprintf(
                    __('The bucket "%s" does not exist. Please verify your bucket name is correct.', 's3-media-sync'),
                    $this->settings['bucket']
                );
                $error_type = "bucket_name";
            }
            else if (strpos($debug_text, "OPERATION_ERROR:") !== false) {
                $simple_message = __('Access denied. Check that your AWS user has sufficient permissions for S3 operations.', 's3-media-sync');
                $error_type = "operation";
            }
            else {
                // Last resort fallback
                $simple_message = __('Error testing S3 access. Please verify your bucket settings and permissions.', 's3-media-sync');
                $error_type = "generic";
            }
        }
        
        // Create unique ID for the error details element with more specific error types
        $error_id = 'aws-error-';
        
        // Add more specific identification to the error ID
        if ($error_type === 'region') {
            $error_id .= 'region-';
        } else if ($error_type === 'bucket_name') {
            $error_id .= 'bucket-name-';
        } else if ($error_type === 'bucket') {
            $error_id .= 'bucket-general-';
        } else if ($error_type === 'credential') {
            $error_id .= 'credential-';
        } else if ($error_type === 'operation') {
            $error_id .= 'operation-';
        } else {
            $error_id .= 'general-';
        }
        
        $error_id .= substr(md5(time()), 0, 6);
        
        // Add technical details in a collapsible section
        $technical_details = sprintf(
            '<div class="s3-media-sync-technical-details">
                <p><a href="#" onclick="jQuery(\'#%s\').toggle(); return false;">%s</a></p>
                <div id="%s" style="display: none;">
                    <p><strong>%s</strong></p>
                    <pre>%s</pre>
                </div>
            </div>',
            $error_id,
            __('Show Technical Details', 's3-media-sync'),
            $error_id,
            __('Debug Steps:', 's3-media-sync'),
            implode("\n", $filtered_debug_info)
        );
        
        add_settings_error(
            's3_media_sync_settings',
            's3-media-sync-test-error',
            $simple_message . $technical_details,
            'error'
        );
    }
} 
