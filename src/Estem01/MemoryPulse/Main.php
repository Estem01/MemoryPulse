<?php

namespace MemoryPulse;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use MemoryPulse\Events\PulseEvent;

class Main extends PluginBase {
    private Config $config;
    private PulseEvent $pulseEvent;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();

        if (!$this->config->get("enabled", true)) {
            $this->getLogger()->info("MemoryPulse is disabled in config.yml.");
            return;
        }

        $this->getLogger()->info("MemoryPulse enabled!");
        $this->pulseEvent = new PulseEvent($this);
        $this->getServer()->getPluginManager()->registerEvents($this->pulseEvent, $this);

        $interval = max(10, $this->config->get("interval", 60));
        $this->getScheduler()->scheduleRepeatingTask(new OptimizationTask($this->pulseEvent), $interval * 20);
    }

    public function getPluginConfig(): Config {
        return $this->config;
    }

    public function getPulseEvent(): PulseEvent {
        return $this->pulseEvent;
    }
}
