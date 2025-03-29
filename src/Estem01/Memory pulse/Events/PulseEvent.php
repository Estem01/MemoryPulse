<?php

namespace MemoryPulse\Events;

use MemoryPulse\Main;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use SplFileObject;

class PulseEvent implements Listener {
    private Main $main;
    private array $config;
    private $memoryBuffer = null;
    private $packetCount = 0;
    private $dynamicBufferSize;
    private $memoryQueue = [];
    private $lastTickTime = 0;
    private $memoryUsageAvg = 0;
    private $packetRateAvg = 0;
    private $dynamicInterval = 0;
    private $optimizationImpact = 0;

    public function __construct(Main $main) {
        $this->main = $main;
        $this->config = $main->getPluginConfig()->getAll();
        $this->dynamicBufferSize = max(1024, $this->config["buffer-size"] ?? 1048576);
        $this->dynamicInterval = max(10, $this->config["interval"] ?? 60);
        $this->lastTickTime = microtime(true);
        $this->initMemoryBuffer();
    }

    public function onPacketSend(DataPacketSendEvent $event): void {
        if (!$this->config["enabled"] ?? true) {
            return;
        }

        $this->packetCount++;
        $packetData = "Packet #" . $this->packetCount;
        $offset = $this->packetCount * strlen($packetData) % $this->dynamicBufferSize;

        $this->memcpySimulated($packetData, $offset);
        $this->updateMetrics();

        if ($this->config["logger"] ?? false) {
            $this->main->getLogger()->info("Packet processed (Total: {$this->packetCount})");
        }
    }

    // Initialize memory buffer (simulates mmap)
    private function initMemoryBuffer(): void {
        try {
            $this->memoryBuffer = new SplFileObject("php://memory", "r+");
            $this->memoryBuffer->fwrite(str_repeat("\0", $this->dynamicBufferSize));
            $this->memoryQueue[] = ['size' => $this->dynamicBufferSize, 'timestamp' => microtime(true)];
            if ($this->config["logger"] ?? false) {
                $this->main->getLogger()->info("Memory buffer initialized: " . ($this->dynamicBufferSize / 1024 / 1024) . "MB");
            }
        } catch (\Exception $e) {
            $this->main->getLogger()->warning("Failed to initialize memory buffer: " . $e->getMessage());
            $this->memoryBuffer = null;
        }
    }

    // Release memory buffer (simulates munmap)
    public function releaseMemoryBuffer(): void {
        if ($this->memoryBuffer !== null) {
            unset($this->memoryBuffer);
            gc_collect_cycles();
            $this->memoryBuffer = null;
            if ($this->config["logger"] ?? false) {
                $this->main->getLogger()->info("Memory buffer released.");
            }
        }
    }

    // Simulate memcpy for efficient data copying
    private function memcpySimulated(string $data, int $offset): void {
        if ($this->memoryBuffer === null || $offset >= $this->dynamicBufferSize) {
            return;
        }
        $this->memoryBuffer->fseek($offset);
        $this->memoryBuffer->fwrite($data);
        $this->memoryQueue[] = ['size' => strlen($data), 'timestamp' => microtime(true)];
    }

    // Update metrics using exponential moving averages
    private function updateMetrics(): void {
        $memoryUsage = memory_get_usage(true) / 1024 / 1024;
        $currentTime = microtime(true);
        $tickDelta = $currentTime - $this->lastTickTime;
        $this->lastTickTime = $currentTime;
        $packetRate = $this->packetCount / max(1, $tickDelta);

        $alpha = 0.1; // Smoothing factor
        $this->memoryUsageAvg = $this->memoryUsageAvg === 0 ? $memoryUsage : ($alpha * $memoryUsage + (1 - $alpha) * $this->memoryUsageAvg);
        $this->packetRateAvg = $this->packetRateAvg === 0 ? $packetRate : ($alpha * $packetRate + (1 - $alpha) * $this->packetRateAvg);
    }

    // Adjust dynamic settings with feedback mechanism
    public function adjustDynamicSettings(): void {
        $memoryThreshold = $this->config["memory-threshold"] ?? 100;
        $packetThreshold = $this->config["packet-threshold"] ?? 50;

        $memoryFactor = $this->memoryUsageAvg / $memoryThreshold;
        $packetFactor = $this->packetRateAvg / $packetThreshold;
        $loadFactor = $packetFactor * 0.4 + $memoryFactor * 0.6;

        // Dynamic buffer size adjustment
        $newBufferSize = max(
            1024,
            (int)(($this->config["buffer-size"] ?? 1048576) * (1 + $loadFactor * 0.5 * (1 - $this->optimizationImpact)))
        );

        if (abs($newBufferSize - $this->dynamicBufferSize) > 1024) {
            $this->dynamicBufferSize = $newBufferSize;
            $this->releaseMemoryBuffer();
            $this->initMemoryBuffer();
            if ($this->config["logger"] ?? false) {
                $this->main->getLogger()->info("Buffer adjusted to " . ($this->dynamicBufferSize / 1024 / 1024) . "MB.");
            }
        }

        // Dynamic interval adjustment
        $newInterval = max(
            10,
            (int)(($this->config["interval"] ?? 60) / (1 + $loadFactor * (1 + $this->optimizationImpact)))
        );

        if (abs($newInterval - $this->dynamicInterval) > 5) {
            $this->dynamicInterval = $newInterval;
            if ($this->config["logger"] ?? false) {
                $this->main->getLogger()->info("Optimization interval adjusted to " . $this->dynamicInterval . "s.");
            }
        }
    }

    // Optimize memory with dynamic syscall simulation
    public function optimizeMemory(): void {
        if (!$this->config["enabled"] ?? true) {
            return;
        }

        $memoryBefore = memory_get_usage(true) / 1024 / 1024;
        $this->adjustDynamicSettings();

        $madviseType = $this->config["default-madvise"] ?? "MADV_DONTNEED";
        if ($this->memoryUsageAvg > ($this->config["memory-threshold"] ?? 100) * 1.5) {
            $madviseType = "MADV_DONTNEED";
        } elseif ($this->packetRateAvg > ($this->config["packet-threshold"] ?? 50)) {
            $madviseType = "MADV_SEQUENTIAL";
        } elseif ($this->memoryUsageAvg < ($this->config["memory-threshold"] ?? 100) * 0.5) {
            $madviseType = "MADV_WILLNEED";
        }

        $this->adviseMemory($madviseType);
        $this->processMemoryQueue();

        gc_collect_cycles();
        gc_mem_caches();

        if ($this->config["opcache-reset"] ?? true && function_exists('opcache_reset') && ini_get('opcache.enable')) {
            opcache_reset();
            if ($this->config["logger"] ?? false) {
                $this->main->getLogger()->info("OPcache reset performed.");
            }
        }

        // Calculate optimization impact
        $memoryAfter = memory_get_usage(true) / 1024 / 1024;
        $this->optimizationImpact = max(0, min(1, ($memoryBefore - $memoryAfter) / max(1, $memoryBefore)));
    }

    // Simulate madvise with dynamic chunk size
    private function adviseMemory(string $advice): void {
        if ($this->memoryBuffer === null) {
            return;
        }

        $chunkSize = max(64, min(1024, (int)($this->dynamicBufferSize / 1024))); // Dynamic chunk size

        switch ($advice) {
            case "MADV_DONTNEED":
                $this->releaseMemoryBuffer();
                $this->initMemoryBuffer();
                if ($this->config["logger"] ?? false) {
                    $this->main->getLogger()->info("MADV_DONTNEED: Buffer reset.");
                }
                break;
            case "MADV_WILLNEED":
                $this->memoryBuffer->fseek(0);
                $this->memoryBuffer->fread($chunkSize);
                if ($this->config["logger"] ?? false) {
                    $this->main->getLogger()->info("MADV_WILLNEED: Buffer prioritized with chunk size $chunkSize.");
                }
                break;
            case "MADV_SEQUENTIAL":
                $this->memoryBuffer->fseek(0);
                $steps = (int)($this->dynamicBufferSize / $chunkSize / 4);
                for ($i = 0; $i < $steps; $i++) {
                    $this->memoryBuffer->fseek($i * $chunkSize * 4);
                    $this->memoryBuffer->fwrite(str_repeat("S", $chunkSize));
                }
                if ($this->config["logger"] ?? false) {
                    $this->main->getLogger()->info("MADV_SEQUENTIAL: Sequential optimization with chunk size $chunkSize.");
                }
                break;
            case "MADV_RANDOM":
                $this->memoryBuffer->fseek(rand(0, $this->dynamicBufferSize - 1));
                $this->memoryBuffer->fread($chunkSize);
                if ($this->config["logger"] ?? false) {
                    $this->main->getLogger()->info("MADV_RANDOM: Random access prepared with chunk size $chunkSize.");
                }
                break;
        }
    }

    // Process memory queue for garbage collection
    private function processMemoryQueue(): void {
        $currentTime = microtime(true);
        $totalFreed = 0;
        $queueSize = count($this->memoryQueue);

        if ($queueSize > 50) {
            for ($i = 0; $i < $queueSize; $i++) {
                if (isset($this->memoryQueue[$i]) && $currentTime - $this->memoryQueue[$i]['timestamp'] > 30) {
                    $totalFreed += $this->memoryQueue[$i]['size'];
                    unset($this->memoryQueue[$i]);
                }
            }
            $this->memoryQueue = array_values($this->memoryQueue);
        }

        if ($totalFreed > 0 && ($this->config["logger"] ?? false)) {
            $this->main->getLogger()->info("Garbage memory freed: " . ($totalFreed / 1024) . "KB.");
        }
    }

    public function getPacketCount(): int {
        return $this->packetCount;
    }

    public function getMemoryUsageAvg(): float {
        return $this->memoryUsageAvg;
    }

    public function getPacketRateAvg(): float {
        return $this->packetRateAvg;
    }

    public function getDynamicInterval(): int {
        return $this->dynamicInterval;
    }

    public function getOptimizationImpact(): float {
        return $this->optimizationImpact;
    }
}
