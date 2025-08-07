<?php

/**
 * Simple logging utility for S3 Media Sync
 */
class S3_Media_Sync_Logger {
	
	/**
	 * Log level constants
	 */
	const DEBUG = 'debug';
	const INFO = 'info';
	const WARNING = 'warning';
	const ERROR = 'error';
	
	/**
	 * Whether debugging is enabled
	 *
	 * @var bool
	 */
	private static $debug_enabled = null;
	
	/**
	 * Check if debugging is enabled
	 *
	 * @return bool
	 */
	private static function is_debug_enabled(): bool {
		if (self::$debug_enabled === null) {
			self::$debug_enabled = defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
		}
		return self::$debug_enabled;
	}
	
	/**
	 * Log a message
	 *
	 * @param string $level   Log level (debug, info, warning, error)
	 * @param string $message Message to log
	 * @param array  $context Additional context data
	 */
	public static function log(string $level, string $message, array $context = []): void {
		// Only log debug messages if debug is enabled
		if ($level === self::DEBUG && !self::is_debug_enabled()) {
			return;
		}
		
		// Format the message
		$formatted_message = self::format_message($level, $message, $context);
		
		// Log using WordPress error_log if available, otherwise use PHP error_log
		if (function_exists('error_log')) {
			error_log($formatted_message);
		}
		
		// Also trigger a WordPress action for other plugins to hook into
		if (function_exists('do_action')) {
			do_action('s3_media_sync_log', $level, $message, $context);
		}
	}
	
	/**
	 * Log a debug message
	 *
	 * @param string $message Message to log
	 * @param array  $context Additional context data
	 */
	public static function debug(string $message, array $context = []): void {
		self::log(self::DEBUG, $message, $context);
	}
	
	/**
	 * Log an info message
	 *
	 * @param string $message Message to log
	 * @param array  $context Additional context data
	 */
	public static function info(string $message, array $context = []): void {
		self::log(self::INFO, $message, $context);
	}
	
	/**
	 * Log a warning message
	 *
	 * @param string $message Message to log
	 * @param array  $context Additional context data
	 */
	public static function warning(string $message, array $context = []): void {
		self::log(self::WARNING, $message, $context);
	}
	
	/**
	 * Log an error message
	 *
	 * @param string $message Message to log
	 * @param array  $context Additional context data
	 */
	public static function error(string $message, array $context = []): void {
		self::log(self::ERROR, $message, $context);
	}
	
	/**
	 * Format a log message
	 *
	 * @param string $level   Log level
	 * @param string $message Message to log
	 * @param array  $context Additional context data
	 * @return string Formatted message
	 */
	private static function format_message(string $level, string $message, array $context = []): string {
		$timestamp = current_time('c');
		$formatted = sprintf('[%s] S3 Media Sync [%s]: %s', $timestamp, strtoupper($level), $message);
		
		// Add context if provided
		if (!empty($context)) {
			$context_string = wp_json_encode($context, JSON_UNESCAPED_SLASHES);
			if ($context_string !== false) {
				$formatted .= ' Context: ' . $context_string;
			}
		}
		
		return $formatted;
	}
	
	/**
	 * Log an exception with full stack trace
	 *
	 * @param \Throwable $exception The exception to log
	 * @param string     $level     Log level (defaults to error)
	 * @param array      $context   Additional context data
	 */
	public static function exception(\Throwable $exception, string $level = self::ERROR, array $context = []): void {
		$context['exception_class'] = get_class($exception);
		$context['file'] = $exception->getFile();
		$context['line'] = $exception->getLine();
		$context['trace'] = $exception->getTraceAsString();
		
		self::log($level, $exception->getMessage(), $context);
	}
}