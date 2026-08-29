<?php
/**
 * Logger Class
 * Two-level logging system (Admin: detailed, User: Toast)
 */

namespace App\Core;

class Logger
{
    private const LEVELS = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4,
    ];

    private string $logFile;
    private int $minLevel;

    public function __construct(int $minLevel = 1)
    {
        $this->logFile = __DIR__ . '/../../storage/logs/system.log';
        $this->minLevel = $minLevel;
        
        // Ensure log directory exists
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    /**
     * Log a message
     */
    public function log(string $level, string $message, array $context = [], ?int $userId = null): void
    {
        if (!isset(self::LEVELS[$level]) || self::LEVELS[$level] < $this->minLevel) {
            return;
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ];

        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    public function debug(string $message, array $context = [], ?int $userId = null): void
    {
        $this->log('debug', $message, $context, $userId);
    }

    public function info(string $message, array $context = [], ?int $userId = null): void
    {
        $this->log('info', $message, $context, $userId);
    }

    public function warning(string $message, array $context = [], ?int $userId = null): void
    {
        $this->log('warning', $message, $context, $userId);
    }

    public function error(string $message, array $context = [], ?int $userId = null): void
    {
        $this->log('error', $message, $context, $userId);
    }

    public function critical(string $message, array $context = [], ?int $userId = null): void
    {
        $this->log('critical', $message, $context, $userId);
    }

    /**
     * Get logs for admin dashboard
     */
    public function getLogs(int $limit = 100, ?string $level = null): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $logs = [];
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach (array_slice(array_reverse($lines), 0, $limit) as $line) {
            $entry = json_decode($line, true);
            if ($entry) {
                if ($level === null || $entry['level'] === strtoupper($level)) {
                    $logs[] = $entry;
                }
            }
        }

        return $logs;
    }

    /**
     * Clear old logs
     */
    public function clearOldLogs(int $days = 30): void
    {
        if (!file_exists($this->logFile)) {
            return;
        }

        $cutoff = strtotime("-{$days} days");
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $newLines = [];

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if ($entry && isset($entry['timestamp'])) {
                $logTime = strtotime($entry['timestamp']);
                if ($logTime > $cutoff) {
                    $newLines[] = $line;
                }
            } else {
                $newLines[] = $line;
            }
        }

        file_put_contents($this->logFile, implode(PHP_EOL, $newLines) . PHP_EOL);
    }
}
