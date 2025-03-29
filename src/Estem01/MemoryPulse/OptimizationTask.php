<?php

namespace Estem01\MemoryPulse;

use pocketmine\scheduler\Task;
use Estem01\MemoryPulse\Events\PulseEvent;

class OptimizationTask extends Task {
    private PulseEvent $pulseEvent;

    public function __construct(PulseEvent $pulseEvent) {
        $this->pulseEvent = $pulseEvent;
    }

    public function onRun(): void {
        $this->pulseEvent->optimizeMemory();

        if ($this->pulseEvent->getMain()->getPluginConfig()->get("logger", false)) {
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;
            $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
            $this->pulseEvent->getMain()->getLogger()->info(sprintf(
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
