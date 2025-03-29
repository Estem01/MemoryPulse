## MemoryPulse

🌌 **MemoryPulse** is a PocketMine-MP plugin that dynamically optimizes server memory and syscalls using techniques inspired by C++/C and Assembly for maximum performance!

[![Poggit](https://poggit.pmmp.io/shield.state/MemoryPulse)](https://poggit.pmmp.io/p/MemoryPulse) [![Download](https://poggit.pmmp.io/shield.dl.total/MemoryPulse)](https://poggit.pmmp.io/p/MemoryPulse)

---

## ❗ Requirements

- **PocketMine-MP API**: 5.0.0 or higher  
- **PHP**: 8.0 or higher  

---

## ❓ How to Install

1. Download the latest `.phar` from the [Releases](https://github.com/Estem01/MemoryPulse/releases) page or [Poggit](https://poggit.pmmp.io/p/MemoryPulse).  
2. Place the `.phar` file in your server's `plugins` folder.  
3. Start or restart your server to generate the `config.yml`.  
4. Edit `config.yml` in `plugins/MemoryPulse` to configure optimization settings.  
5. Enjoy! MemoryPulse will optimize your server automatically.

---

## ✨ Features

- **Dynamic Memory Optimization**: Adjusts buffer size and strategy based on memory usage and packet rate.  
- **Garbage Memory Management**: Tracks and frees unused memory allocations efficiently.  
- **Syscall Simulation**: Mimics C++/Assembly functions like `mmap`, `madvise`, `memcpy`, and `munmap`.  
- **Packet Optimization**: Monitors and optimizes packet handling globally.  
- **Flexible Settings**: Configure base intervals, buffer size, thresholds, and memory advice.  
- **Optional Logging**: Track optimization actions and stats in the console.  

---

## 📜 Versions

- **Version 1.0.0**
  - Initial release with advanced memory and syscall optimization.  
  - Compatible with PocketMine-MP API 5.0.0.  

---

## ⚙️ Configuration

The default `config.yml` is generated in `plugins/MemoryPulse`. Customize it to fit your server:

```yaml
# Enable or disable the plugin
enabled: true

# Base optimization interval in seconds (will be dynamically adjusted)
interval: 60

# Base memory buffer size in bytes (will be dynamically adjusted)
buffer-size: 1048576  # 1MB

# Initial memory threshold in MB (will adapt)
memory-threshold: 100

# Initial packet rate threshold (packets per second, will adapt)
packet-threshold: 50

# Default memory advice type (MADV_DONTNEED, MADV_WILLNEED, MADV_SEQUENTIAL, MADV_RANDOM)
default-madvise: "MADV_DONTNEED"

# Enable OPcache reset if available
opcache-reset: true

# Enable plugin logger
logger: false
```

### Options Explained
- `enabled`: Enable or disable the plugin entirely.  
- `interval`: Base time between optimizations (seconds), dynamically adjusted.  
- `buffer-size`: Base memory buffer size (bytes), dynamically adjusted.  
- `memory-threshold`: Initial memory usage level (MB) triggering aggressive optimization, will adapt.  
- `packet-threshold`: Initial packet rate (per second) influencing optimization behavior, will adapt.  
- `default-madvise`: Default memory advice strategy when dynamic conditions aren't met.  
- `opcache-reset`: Enable OPcache reset to free memory (if available).  
- `logger`: Enable logging of optimization actions and stats.  

---

## 🔒 Permissions

- None required! MemoryPulse operates globally without player-specific interactions.

---

## ⭐ Join Our Community

Get support, updates, and share your ideas on our Discord server!  
👉 **[Discord](https://discord.gg/your-discord-invite)** 

---

## 🛠️ Contributing

Want to improve MemoryPulse?  
- Report bugs or suggest features in the [Issues](https://github.com/Estem01/MemoryPulse/issues) tab.  
- Submit code enhancements via [Pull Requests](https://github.com/Estem01/MemoryPulse/pulls).  

Check the [commits](https://github.com/Estem01/MemoryPulse/commits/main) for the latest changes!

---

## 📝 License

This project is licensed under the [Apache License](https://github.com/Estem01/MemoryPulse/blob/main/LICENSE).  

---

## 👥 Authors

- [Estem01](https://github.com/Estem01)

---
