<?php

namespace WPCOM_VIP;

/**
 * Couchbase extension stubs
 * Gathered from https://docs.couchbase.com/sdk-api/couchbase-php-client-2.3.0/index.html
 * Maintainer: sergey@couchbase.com
 *
 * https://github.com/couchbase/php-couchbase/tree/master/api
 */
use function WPCOM_VIP\Couchbase\fastlzCompress as couchbase_fastlz_compress;
use function WPCOM_VIP\Couchbase\fastlzDecomress as couchbase_fastlz_decompress;
use function WPCOM_VIP\Couchbase\zlibCompress as couchbase_zlib_compress;
use function WPCOM_VIP\Couchbase\zlibDecomress as couchbase_zlib_decompress;
use function WPCOM_VIP\Couchbase\passthruEncoder as couchbase_passthru_encoder;
use function WPCOM_VIP\Couchbase\passthruDecoder as couchbase_passthru_decoder;
use function WPCOM_VIP\Couchbase\defaultEncoder as couchbase_default_encoder;
use function WPCOM_VIP\Couchbase\defaultDecoder as couchbase_default_decoder;
use function WPCOM_VIP\Couchbase\basicEncoderV1 as couchbase_basic_encoder_v1;
use function WPCOM_VIP\Couchbase\basicDecoderV1 as couchbase_basic_decoder_v1;
\class_alias("Couchbase\\Cluster", "WPCOM_VIP\\CouchbaseCluster");
\class_alias("Couchbase\\Bucket", "WPCOM_VIP\\CouchbaseBucket");
\class_alias("Couchbase\\MutationToken", "WPCOM_VIP\\CouchbaseMutationToken");
\class_alias("Couchbase\\MutationState", "WPCOM_VIP\\CouchbaseMutationState");
\class_alias("Couchbase\\BucketManager", "WPCOM_VIP\\CouchbaseBucketManager");
\class_alias("Couchbase\\ClusterManager", "WPCOM_VIP\\CouchbaseClusterManager");
\class_alias("Couchbase\\LookupInBuilder", "WPCOM_VIP\\CouchbaseLookupInBuilder");
\class_alias("Couchbase\\MutateInBuilder", "WPCOM_VIP\\CouchbaseMutateInBuilder");
\class_alias("Couchbase\\N1qlQuery", "WPCOM_VIP\\CouchbaseN1qlQuery");
\class_alias("Couchbase\\SearchQuery", "WPCOM_VIP\\CouchbaseSearchQuery");
\class_alias("Couchbase\\SearchQueryPart", "WPCOM_VIP\\CouchbaseAbstractSearchQuery");
\class_alias("Couchbase\\QueryStringSearchQuery", "WPCOM_VIP\\CouchbaseStringSearchQuery");
\class_alias("Couchbase\\MatchSearchQuery", "WPCOM_VIP\\CouchbaseMatchSearchQuery");
\class_alias("Couchbase\\MatchPhraseSearchQuery", "WPCOM_VIP\\CouchbaseMatchPhraseSearchQuery");
\class_alias("Couchbase\\PrefixSearchQuery", "WPCOM_VIP\\CouchbasePrefixSearchQuery");
\class_alias("Couchbase\\RegexpSearchQuery", "WPCOM_VIP\\CouchbaseRegexpSearchQuery");
\class_alias("Couchbase\\NumericRangeSearchQuery", "WPCOM_VIP\\CouchbaseNumericRangeSearchQuery");
\class_alias("Couchbase\\DisjunctionSearchQuery", "WPCOM_VIP\\CouchbaseDisjunctionSearchQuery");
\class_alias("Couchbase\\DateRangeSearchQuery", "WPCOM_VIP\\CouchbaseDateRangeSearchQuery");
\class_alias("Couchbase\\ConjunctionSearchQuery", "WPCOM_VIP\\CouchbaseConjunctionSearchQuery");
\class_alias("Couchbase\\BooleanSearchQuery", "WPCOM_VIP\\CouchbaseBooleanSearchQuery");
\class_alias("Couchbase\\WildcardSearchQuery", "WPCOM_VIP\\CouchbaseWildcardSearchQuery");
\class_alias("Couchbase\\DocIdSearchQuery", "WPCOM_VIP\\CouchbaseDocIdSearchQuery");
\class_alias("Couchbase\\BooleanFieldSearchQuery", "WPCOM_VIP\\CouchbaseBooleanFieldSearchQuery");
\class_alias("Couchbase\\TermSearchQuery", "WPCOM_VIP\\CouchbaseTermSearchQuery");
\class_alias("Couchbase\\PhraseSearchQuery", "WPCOM_VIP\\CouchbasePhraseSearchQuery");
\class_alias("Couchbase\\MatchAllSearchQuery", "WPCOM_VIP\\CouchbaseMatchAllSearchQuery");
\class_alias("Couchbase\\MatchNoneSearchQuery", "WPCOM_VIP\\CouchbaseMatchNoneSearchQuery");
\class_alias("Couchbase\\DateRangeSearchFacet", "WPCOM_VIP\\CouchbaseDateRangeSearchFacet");
\class_alias("Couchbase\\NumericRangeSearchFacet", "WPCOM_VIP\\CouchbaseNumericRangeSearchFacet");
\class_alias("Couchbase\\TermSearchFacet", "WPCOM_VIP\\CouchbaseTermSearchFacet");
\class_alias("Couchbase\\SearchFacet", "WPCOM_VIP\\CouchbaseSearchFacet");
\class_alias("Couchbase\\ViewQuery", "WPCOM_VIP\\CouchbaseViewQuery");
\class_alias("Couchbase\\DocumentFragment", "WPCOM_VIP\\CouchbaseDocumentFragment");
\class_alias("Couchbase\\Document", "WPCOM_VIP\\CouchbaseMetaDoc");
\class_alias("Couchbase\\Exception", "WPCOM_VIP\\CouchbaseException");
\class_alias("Couchbase\\ClassicAuthenticator", "WPCOM_VIP\\CouchbaseAuthenticator");
\define("COUCHBASE_PERSISTTO_MASTER", 1);
\define("COUCHBASE_PERSISTTO_ONE", 1);
\define("COUCHBASE_PERSISTTO_TWO", 2);
\define("COUCHBASE_PERSISTTO_THREE", 4);
\define("COUCHBASE_REPLICATETO_ONE", 16);
\define("COUCHBASE_REPLICATETO_TWO", 32);
\define("COUCHBASE_REPLICATETO_THREE", 64);
\define("COUCHBASE_SUCCESS", 0);
\define("COUCHBASE_AUTH_CONTINUE", 1);
\define("COUCHBASE_AUTH_ERROR", 2);
\define("COUCHBASE_DELTA_BADVAL", 3);
\define("COUCHBASE_E2BIG", 4);
\define("COUCHBASE_EBUSY", 5);
\define("COUCHBASE_EINTERNAL", 6);
\define("COUCHBASE_EINVAL", 7);
\define("COUCHBASE_ENOMEM", 8);
\define("COUCHBASE_ERANGE", 9);
\define("COUCHBASE_ERROR", 10);
\define("COUCHBASE_ETMPFAIL", 11);
\define("COUCHBASE_KEY_EEXISTS", 12);
\define("COUCHBASE_KEY_ENOENT", 13);
\define("COUCHBASE_DLOPEN_FAILED", 14);
\define("COUCHBASE_DLSYM_FAILED", 15);
\define("COUCHBASE_NETWORK_ERROR", 16);
\define("COUCHBASE_NOT_MY_VBUCKET", 17);
\define("COUCHBASE_NOT_STORED", 18);
\define("COUCHBASE_NOT_SUPPORTED", 19);
\define("COUCHBASE_UNKNOWN_COMMAND", 20);
\define("COUCHBASE_UNKNOWN_HOST", 21);
\define("COUCHBASE_PROTOCOL_ERROR", 22);
\define("COUCHBASE_ETIMEDOUT", 23);
\define("COUCHBASE_CONNECT_ERROR", 24);
\define("COUCHBASE_BUCKET_ENOENT", 25);
\define("COUCHBASE_CLIENT_ENOMEM", 26);
\define("COUCHBASE_CLIENT_ENOCONF", 27);
\define("COUCHBASE_EBADHANDLE", 28);
\define("COUCHBASE_SERVER_BUG", 29);
\define("COUCHBASE_PLUGIN_VERSION_MISMATCH", 30);
\define("COUCHBASE_INVALID_HOST_FORMAT", 31);
\define("COUCHBASE_INVALID_CHAR", 32);
\define("COUCHBASE_DURABILITY_ETOOMANY", 33);
\define("COUCHBASE_DUPLICATE_COMMANDS", 34);
\define("COUCHBASE_NO_MATCHING_SERVER", 35);
\define("COUCHBASE_BAD_ENVIRONMENT", 36);
\define("COUCHBASE_BUSY", 37);
\define("COUCHBASE_INVALID_USERNAME", 38);
\define("COUCHBASE_CONFIG_CACHE_INVALID", 39);
\define("COUCHBASE_SASLMECH_UNAVAILABLE", 40);
\define("COUCHBASE_TOO_MANY_REDIRECTS", 41);
\define("COUCHBASE_MAP_CHANGED", 42);
\define("COUCHBASE_INCOMPLETE_PACKET", 43);
\define("COUCHBASE_ECONNREFUSED", 44);
\define("COUCHBASE_ESOCKSHUTDOWN", 45);
\define("COUCHBASE_ECONNRESET", 46);
\define("COUCHBASE_ECANTGETPORT", 47);
\define("COUCHBASE_EFDLIMITREACHED", 48);
\define("COUCHBASE_ENETUNREACH", 49);
\define("COUCHBASE_ECTL_UNKNOWN", 50);
\define("COUCHBASE_ECTL_UNSUPPMODE", 51);
\define("COUCHBASE_ECTL_BADARG", 52);
\define("COUCHBASE_EMPTY_KEY", 53);
\define("COUCHBASE_SSL_ERROR", 54);
\define("COUCHBASE_SSL_CANTVERIFY", 55);
\define("COUCHBASE_SCHEDFAIL_INTERNAL", 56);
\define("COUCHBASE_CLIENT_FEATURE_UNAVAILABLE", 57);
\define("COUCHBASE_OPTIONS_CONFLICT", 58);
\define("COUCHBASE_HTTP_ERROR", 59);
\define("COUCHBASE_DURABILITY_NO_MUTATION_TOKENS", 60);
\define("COUCHBASE_UNKNOWN_MEMCACHED_ERROR", 61);
\define("COUCHBASE_MUTATION_LOST", 62);
\define("COUCHBASE_SUBDOC_PATH_ENOENT", 63);
\define("COUCHBASE_SUBDOC_PATH_MISMATCH", 64);
\define("COUCHBASE_SUBDOC_PATH_EINVAL", 65);
\define("COUCHBASE_SUBDOC_PATH_E2BIG", 66);
\define("COUCHBASE_SUBDOC_DOC_E2DEEP", 67);
\define("COUCHBASE_SUBDOC_VALUE_CANTINSERT", 68);
\define("COUCHBASE_SUBDOC_DOC_NOTJSON", 69);
\define("COUCHBASE_SUBDOC_NUM_ERANGE", 70);
\define("COUCHBASE_SUBDOC_BAD_DELTA", 71);
\define("COUCHBASE_SUBDOC_PATH_EEXISTS", 72);
\define("COUCHBASE_SUBDOC_MULTI_FAILURE", 73);
\define("COUCHBASE_SUBDOC_VALUE_E2DEEP", 74);
\define("COUCHBASE_EINVAL_MCD", 75);
\define("COUCHBASE_EMPTY_PATH", 76);
\define("COUCHBASE_UNKNOWN_SDCMD", 77);
\define("COUCHBASE_ENO_COMMANDS", 78);
\define("COUCHBASE_QUERY_ERROR", 79);
\define("COUCHBASE_TMPFAIL", 11);
\define("COUCHBASE_KEYALREADYEXISTS", 12);
\define("COUCHBASE_KEYNOTFOUND", 13);
