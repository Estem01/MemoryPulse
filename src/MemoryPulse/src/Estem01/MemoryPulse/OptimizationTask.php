<?php

namespace MemoryPulse;

use pocketmine\scheduler\Task;

class OptimizationTask extends Task {
    private PulseEvent $pulseEvent;

    public function __construct(PulseEvent $pulseEvent) {
        $this->pulseEvent = $pulseEvent;
    }

    public function onRun(): void {
        $this->pulseEvent->optimizeMemory();

        if ($this->pulseEvent->main->getPluginConfig()->get("logger", false)) {
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;
            $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
            $this->pulseEvent->main->getLogger()->info(sprintf(
                "Memory stats - Usage: %.2f MB | Peak: %.2f MB | Packets: %d | Avg Usage: %.2f MB | Avg Packet Rate: %.2f p/s | Interval: %ds | Impact: %.2f",
                $memoryUsage,
                $peakMemory,
                $this->pulseEvent->getPacketCount(),
                $this->pulseEvent->getMemoryUsageAvg(),
                $this->pulseEvent->getPacketRateAvg(),
                $this->pulseEvent->getDynamicInterval(),
                $this->pulseEvent->getOptimizationImpact()
            ));
        }
    }
}
