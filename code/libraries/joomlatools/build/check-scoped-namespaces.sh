#!/usr/bin/env bash
#
# Regression guard: fails if any source file references the bundled third-party
# libraries through their ORIGINAL global namespaces (Cron\, Imagine\) instead
# of the scoped Joomlatools\ prefix.
#
# Loading our copies under the global namespaces collides with the host CMS's
# own copies (e.g. Joomla ships Cron\CronExpression). Always use the prefix:
#     \Joomlatools\Cron\CronExpression
#     \Joomlatools\Imagine\...
#
# Run from CI or before a release. Exits non-zero on any violation.
#
set -euo pipefail
ROOT="${1:-$(cd "$(dirname "$0")/../../../.." && pwd)}"

# Match a bare leading-backslash (or namespace-separated) reference to the
# unscoped libs, but NOT the scoped Joomlatools\ form.
PATTERN='(^|[^\\A-Za-z0-9_])\\?(Cron|Imagine)\\'

hits="$(grep -rnE "$PATTERN" "$ROOT" \
    --include='*.php' \
    --exclude-dir=vendor \
    --exclude-dir=vendor-prefixed \
    --exclude-dir=.history \
    2>/dev/null | grep -v 'Joomlatools\\' || true)"

if [ -n "$hits" ]; then
    echo "ERROR: unscoped references to bundled libraries found." >&2
    echo "Use the \\Joomlatools\\ prefix instead:" >&2
    echo "$hits" >&2
    exit 1
fi

echo "OK: no unscoped Cron\\ or Imagine\\ references."
