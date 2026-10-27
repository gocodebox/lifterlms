#!/usr/bin/env bash
# Fail the build when libraries/lifterlms-rest changed against trunk without a version bump.
# Compare to trunk, not dev: an unreleased bump can already be on dev (for example 1.1.1)
# while trunk is still the last release. A version that differs from trunk is enough.
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
cd "$root"

class_file="libraries/lifterlms-rest/class-lifterlms-rest-api.php"
pkg_file="libraries/lifterlms-rest/package.json"

read_class_version() {
	php -r '
		$file = $argv[1];
		$src = file_get_contents( $file );
		if ( ! preg_match( "/public \\\$version = '\''([^'\'']+)'\'';/", $src, $m ) ) {
			fwrite( STDERR, "Could not read LifterLMS_REST_API::\$version from {$file}\n" );
			exit( 1 );
		}
		echo $m[1];
	' "$1"
}

read_pkg_version() {
	php -r '
		$pkg = json_decode( file_get_contents( $argv[1] ), true );
		if ( empty( $pkg["version"] ) ) {
			fwrite( STDERR, "Could not read version from {$argv[1]}\n" );
			exit( 1 );
		}
		echo $pkg["version"];
	' "$1"
}

current_class="$(read_class_version "$class_file")"
current_pkg="$(read_pkg_version "$pkg_file")"

if [ "$current_class" != "$current_pkg" ]; then
	echo "REST library version mismatch: class is ${current_class}, package.json is ${current_pkg}." >&2
	exit 1
fi

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
	echo "check:rest-version: not a git checkout, skipping diff against trunk." >&2
	exit 0
fi

base_ref="origin/trunk"
if ! git fetch --depth=1 origin trunk; then
	if git rev-parse --verify --quiet refs/remotes/origin/trunk >/dev/null; then
		echo "check:rest-version: could not fetch origin/trunk; using the local ref." >&2
	elif git rev-parse --verify --quiet refs/heads/trunk >/dev/null; then
		base_ref="trunk"
		echo "check:rest-version: could not fetch origin/trunk; using local trunk." >&2
	else
		echo "check:rest-version: could not resolve trunk." >&2
		exit 1
	fi
fi

base_src="$(git show "${base_ref}:${class_file}")"
base_class="$(php -r '
	$src = stream_get_contents( STDIN );
	if ( ! preg_match( "/public \\\$version = '\''([^'\'']+)'\'';/", $src, $m ) ) {
		fwrite( STDERR, "Could not read LifterLMS_REST_API::\$version from trunk.\n" );
		exit( 1 );
	}
	echo $m[1];
' <<<"$base_src")"

# Version files themselves do not count. A class-file diff that only changes the
# version property does not count either.
substantive=0
while IFS= read -r file; do
	[ -z "$file" ] && continue
	case "$file" in
		libraries/lifterlms-rest/package.json|libraries/lifterlms-rest/package-lock.json)
			continue
			;;
		libraries/lifterlms-rest/class-lifterlms-rest-api.php)
			if git diff -U0 "$base_ref" -- "$file" | grep -E '^[+-]' | grep -Ev '^(---|\+\+\+)' | grep -Ev '^[+-][[:space:]]*public \$version = ' | grep -q .; then
				substantive=1
			fi
			;;
		*)
			substantive=1
			;;
	esac
done < <(git diff --name-only "$base_ref" -- libraries/lifterlms-rest; git ls-files --others --exclude-standard -- libraries/lifterlms-rest)

if [ "$substantive" -eq 1 ] && [ "$current_class" = "$base_class" ]; then
	echo "libraries/lifterlms-rest changed against trunk but the version is still ${current_class}. Bump LifterLMS_REST_API::\$version and libraries/lifterlms-rest/package.json." >&2
	exit 1
fi

echo "REST library version ${current_class} matches package.json."
