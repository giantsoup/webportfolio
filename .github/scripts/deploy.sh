#!/usr/bin/env bash

set -Eeuo pipefail

: "${DEPLOY_PATH:?DEPLOY_PATH must be set.}"

RELEASE_ARCHIVE="${RELEASE_ARCHIVE:-/tmp/webportfolio-release.tar.gz}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

release_name="$(date +%Y%m%d%H%M%S)"
release_dir="${DEPLOY_PATH}/releases/${release_name}"
shared_dir="${DEPLOY_PATH}/shared"
current_dir="${DEPLOY_PATH}/current"
release_activated=false

cleanup_failed_release() {
    if [[ "${release_activated}" == "false" && -d "${release_dir}" ]]; then
        rm -rf "${release_dir}"
    fi
}

trap cleanup_failed_release ERR

mkdir -p "${DEPLOY_PATH}/releases"
mkdir -p "${shared_dir}/storage/app/public"
mkdir -p "${shared_dir}/storage/framework/cache"
mkdir -p "${shared_dir}/storage/framework/sessions"
mkdir -p "${shared_dir}/storage/framework/views"
mkdir -p "${shared_dir}/storage/logs"
mkdir -p "${shared_dir}/bootstrap/cache"

if [[ ! -f "${shared_dir}/.env" ]]; then
    echo "Missing shared environment file at ${shared_dir}/.env" >&2
    exit 1
fi

mkdir -p "${release_dir}"
tar -xzf "${RELEASE_ARCHIVE}" -C "${release_dir}"

rm -rf "${release_dir}/storage"
ln -sfn "${shared_dir}/storage" "${release_dir}/storage"
ln -sfn "${shared_dir}/.env" "${release_dir}/.env"

mkdir -p "${release_dir}/bootstrap/cache"
find \
    "${shared_dir}/storage/app" \
    "${shared_dir}/storage/framework" \
    "${shared_dir}/bootstrap/cache" \
    "${release_dir}/bootstrap/cache" \
    -type d \
    -exec chmod ug+rwx {} +

cd "${release_dir}"

"${COMPOSER_BIN}" install --no-dev --prefer-dist --no-interaction --optimize-autoloader
"${PHP_BIN}" artisan migrate --force
"${PHP_BIN}" artisan optimize:clear
"${PHP_BIN}" artisan optimize
"${PHP_BIN}" artisan storage:link || true

ln -sfn "${release_dir}" "${current_dir}"
release_activated=true

cd "${current_dir}"
"${PHP_BIN}" artisan reload || "${PHP_BIN}" artisan queue:restart
"${PHP_BIN}" artisan schedule:interrupt || true

mapfile -t releases < <(find "${DEPLOY_PATH}/releases" -mindepth 1 -maxdepth 1 -type d | sort)

if (( ${#releases[@]} > KEEP_RELEASES )); then
    for old_release in "${releases[@]:0:${#releases[@]}-KEEP_RELEASES}"; do
        rm -rf "${old_release}"
    done
fi

rm -f "${RELEASE_ARCHIVE}"

trap - ERR

echo "Deployment completed: ${release_name}"
