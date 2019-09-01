# rrlog

A PHP port of the [rrlog](https://github.com/BZFlag-Dev/bzflag/blob/2.4/misc/rrlog.cxx) project to allow server owners to convert replay files into flat JSON files. This tool is built around the [bzflag-networking.php](https://github.com/allejo/bzflag-networking.php) library that unpacks BZFlag packets into PHP objects.

## Usage

You need PHP 7.1+ and download the latest PHAR from our [GitHub releases](https://github.com/allejo/rrlog/releases/latest).

```bash
./rrlog.phar [command] [options]
```

### `export` Command

Use the `export` command to translate a replay file from raw packets into a JSON representation.

```bash
./rrlog.phar export <path to replay>

# Exclude chat messages
./rrlog.phar export <path to replay> --blacklist MsgMessage

# Include only chat messages
./rrlog.phar export <path to replay> --whitelist MsgMessage
```

#### Available Flags

- `--blacklist` - a comma separated list of packet types to exclude from the JSON file
- `--whitelist` - a comma separated list of the only packet types that will be written to the JSON file
- `--format` or `-f` - The format of the output file; only `json` is supported right now
- `--output` or `-o` - The location of where the output file will be written to. By default, the output file will be at the same location of the replay file with a `.json` extension added to the filename.

##### Notes

- For packet types, the official packet names used in the BZFS source code are used
- The `--blacklist` and `--whitelist` flags cannot be used together

## License

[MIT](./LICENSE.md)
