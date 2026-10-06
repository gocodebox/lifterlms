#!/usr/bin/env bash
# Fail the build when libraries/lifterlms-rest changed against dev without a version bump.
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
	echo "check:rest-version: not a git checkout, skipping diff against dev." >&2
	exit 0
fi

if ! git rev-parse --verify --quiet dev >/dev/null; then
	git fetch origin dev:refs/heads/dev --depth=1
fi

base_src="$(git show "dev:${class_file}")"
base_class="$(php -r '
	$src = stream_get_contents( STDIN );
	if ( ! preg_match( "/public \\\$version = '\''([^'\'']+)'\'';/", $src, $m ) ) {
		fwrite( STDERR, "Could not read LifterLMS_REST_API::\$version from dev.\n" );
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
			if git diff -U0 dev -- "$file" | grep -E '^[+-]' | grep -Ev '^(---|\+\+\+)' | grep -Ev '^[+-][[:space:]]*public \$version = ' | grep -q .; then
				substantive=1
			fi
			;;
		*)
			substantive=1
			;;
	esac
done < <(git diff --name-only dev -- libraries/lifterlms-rest; git ls-files --others --exclude-standard -- libraries/lifterlms-rest)

if [ "$substantive" -eq 1 ] && [ "$current_class" = "$base_class" ]; then
	echo "libraries/lifterlms-rest changed against dev but the version is still ${current_class}. Bump LifterLMS_REST_API::\$version and libraries/lifterlms-rest/package.json." >&2
	exit 1
fi

echo "REST library version ${current_class} matches package.json."
