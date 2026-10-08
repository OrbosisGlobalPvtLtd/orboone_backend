<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class LaravelLogViewerController extends Controller
{
    /**
     * Display the log viewer interface.
     */
    public function index(Request $request)
    {
        $logPath = storage_path('logs');
        $files = [];

        if (File::exists($logPath)) {
            $files = collect(File::files($logPath))
                ->filter(function ($file) {
                    return strtolower($file->getExtension()) === 'log';
                })
                ->map(function ($file) {
                    return [
                        'name' => $file->getFilename(),
                        'path' => $file->getPathname(),
                        'size' => $file->getSize(),
                        'size_formatted' => $this->formatBytes($file->getSize()),
                        'updated_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    ];
                })
                ->sortByDesc('updated_at')
                ->values()
                ->toArray();
        }

        $selectedFileName = $request->get('file');
        if (!$selectedFileName && !empty($files)) {
            $selectedFileName = $files[0]['name'];
        }

        $selectedFile = null;
        if ($selectedFileName) {
            foreach ($files as $f) {
                if ($f['name'] === $selectedFileName) {
                    $selectedFile = $f;
                    break;
                }
            }
        }

        $parsedLogs = [];
        $stats = [
            'total' => 0,
            'emergency' => 0,
            'alert' => 0,
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'notice' => 0,
            'info' => 0,
            'debug' => 0,
        ];

        if ($selectedFile && File::exists($selectedFile['path'])) {
            $rawContent = File::get($selectedFile['path']);
            $parsedLogs = $this->parseLogContent($rawContent);

            foreach ($parsedLogs as $log) {
                $level = strtolower($log['level']);
                if (isset($stats[$level])) {
                    $stats[$level]++;
                }
                $stats['total']++;
            }

            // Level filter
            $levelFilter = strtolower($request->get('level', 'all'));
            if ($levelFilter !== 'all') {
                $parsedLogs = array_filter($parsedLogs, function ($item) use ($levelFilter) {
                    return strtolower($item['level']) === $levelFilter;
                });
            }

            // Search filter
            $search = trim($request->get('search', ''));
            if ($search !== '') {
                $parsedLogs = array_filter($parsedLogs, function ($item) use ($search) {
                    return stripos($item['header'], $search) !== false
                        || stripos($item['stacktrace'], $search) !== false
                        || stripos($item['context'], $search) !== false;
                });
            }

            $parsedLogs = array_values($parsedLogs);
        }

        return view('settings.laravel_log_viewer', [
            'files' => $files,
            'selectedFile' => $selectedFile,
            'selectedFileName' => $selectedFileName,
            'logs' => $parsedLogs,
            'stats' => $stats,
            'currentLevel' => $request->get('level', 'all'),
            'currentSearch' => $request->get('search', ''),
        ]);
    }

    /**
     * Download specified log file.
     */
    public function download(Request $request)
    {
        $fileName = basename($request->get('file', ''));
        $filePath = storage_path('logs/' . $fileName);

        if (!$fileName || !File::exists($filePath)) {
            return back()->with('fail', 'Log file not found.');
        }

        return Response::download($filePath);
    }

    /**
     * Clear / empty specified log file.
     */
    public function clear(Request $request)
    {
        $fileName = basename($request->get('file', ''));
        $filePath = storage_path('logs/' . $fileName);

        if (!$fileName || !File::exists($filePath)) {
            return back()->with('fail', 'Log file not found.');
        }

        File::put($filePath, '');

        return back()->with('success', "Log file '{$fileName}' has been cleared.");
    }

    /**
     * Delete log file.
     */
    public function delete(Request $request)
    {
        $fileName = basename($request->get('file', ''));
        $filePath = storage_path('logs/' . $fileName);

        if (!$fileName || !File::exists($filePath)) {
            return back()->with('fail', 'Log file not found.');
        }

        File::delete($filePath);

        return redirect()->route('log-viewer.index')->with('success', "Log file '{$fileName}' has been deleted.");
    }

    /**
     * Parse raw log content into formatted array.
     */
    private function parseLogContent(string $content): array
    {
        $logs = [];
        $pattern = '/\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[\d\.\+\:]*)\]\s*([a-zA-Z0-9_]+)\.([A-Z]+):\s*/';

        $entries = preg_split($pattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $count = count($entries);
        for ($i = 0; $i < $count; $i++) {
            if (isset($entries[$i]) && preg_match('/^\d{4}-\d{2}-\d{2}/', $entries[$i])) {
                $timestamp = $entries[$i];
                $env = $entries[$i + 1] ?? 'production';
                $level = strtoupper($entries[$i + 2] ?? 'ERROR');
                $messageBlock = $entries[$i + 3] ?? '';

                $i += 3;

                $lines = explode("\n", trim($messageBlock));
                $header = $lines[0] ?? '';

                $stacktraceLines = [];
                $contextLines = [];

                foreach (array_slice($lines, 1) as $line) {
                    $trimmed = trim($line);
                    if (str_starts_with($trimmed, '#') || str_starts_with($trimmed, '[previous exception]')) {
                        $stacktraceLines[] = $line;
                    } else {
                        $contextLines[] = $line;
                    }
                }

                $logs[] = [
                    'id' => md5($timestamp . $header . $i),
                    'timestamp' => $timestamp,
                    'env' => $env,
                    'level' => $level,
                    'header' => $header,
                    'context' => implode("\n", $contextLines),
                    'stacktrace' => implode("\n", $stacktraceLines),
                ];
            }
        }

        return array_reverse($logs);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
