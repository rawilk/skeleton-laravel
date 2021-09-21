#!/bin/bash
# 'return' when run as "source <script>" or ". <script>", 'exit' otherwise
[[ "$0" != "${BASH_SOURCE[0]}" ]] && safe_exit="return" || safe_exit="exit"

script_name=$(basename "$0")

ask_question() {
    # ask_question <question> <default>
    local ANSWER
    read -r -p "$1 ($2): " ANSWER
    echo "${ANSWER:-$2}"
}

confirm() {
    # confirm <question> (default = N)
    local ANSWER
    read -r -p "$1 (y/N): " -n 1 ANSWER
    echo " "
    [[ "$ANSWER" =~ ^[Yy]$ ]]
}

slugify() {
    # slugify <input> <separator>
    separator="$2"
    [[ -z "$separator" ]] && separator="-"
    echo "$1" |
        tr '[:upper:]' '[:lower:]' |
        tr 'àáâäæãåāçćčèéêëēėęîïííīįìłñńôöòóœøōõßśšûüùúūÿžźż' 'aaaaaaaaccceeeeeeeiiiiiiilnnoooooooosssuuuuuyzzz' |
        awk '{
        gsub(/[\[\]@#$%^&*;,.:()<>!?\/+=_]/," ",$0);
        gsub(/^  */,"",$0);
        gsub(/  *$/,"",$0);
        gsub(/  */,"-",$0);
        gsub(/[^a-z0-9\-]/,"");
        print;
        }' |
        sed "s/-/$separator/g"
}

titlecase() {
    # titlecase <input> <separator>
    separator="${2:-}"
    echo "$1" |
        tr '[:upper:]' '[:lower:]' |
        tr 'àáâäæãåāçćčèéêëēėęîïííīįìłñńôöòóœøōõßśšûüùúūÿžźż' 'aaaaaaaaccceeeeeeeiiiiiiilnnoooooooosssuuuuuyzzz' |
        awk '{ gsub(/[\[\]@#$%^&*;,.:()<>!?\/+=_-]/," ",$0); print $0; }' |
        awk '{
        for (i=1; i<=NF; ++i) {
            $i = toupper(substr($i,1,1)) tolower(substr($i,2))
        };
        print $0;
        }' |
        sed "s/ /$separator/g"
}

git_name=$(git config user.name)
author_name=$(ask_question "Author name" "$git_name")

git_email=$(git config user.email)
author_email=$(ask_question "Author email" "$git_email")

username_guess=$(git config remote.origin.url | cut -d: -f2-)
username_guess=$(dirname "$username_guess")
username_guess=$(basename "$username_guess")
author_username=$(ask_question "Author username" "$username_guess")

current_directory=$(pwd)
folder_name=$(basename "$current_directory")

vendor_name=$(ask_question "Vendor name" "$author_username")
vendor_slug=$(slugify "$vendor_name")
VendorName=$(titlecase "$vendor_name" "")

project_name=$(ask_question "Project name")
project_slug=$(slugify "$project_name" "-")

project_description=$(ask_question "Project description" "This is my project")

echo -e "-----"
echo -e "Author  : $author_name ($author_username, $author_email)"
echo -e "Vendor  : $vendor_name ($vendor_slug)"
echo -e "Project : $project_name ($project_slug) <$project_description>"
echo -e "-----"

files=$(grep -E -r -l -i ":author|:vendor|:project|:short|rawilk|skeleton" --exclude-dir=vendor ./* ./.github/* | grep -v "$script_name")

echo "This script will replace the above values in all relevant files in the project directory."

if ! confirm "Modify files?"; then
    $safe_exit 1
fi

grep -E -r -l -i ":author|:vendor|:project|VendorName|skeleton|vendor_name|vendor_slug|author@domain.com" --exclude-dir=vendor ./* ./.github/* \
| grep -v "$script_name" \
| while read -r file ; do
    new_file="$file"
    new_file="${new_file//Skeleton/$ClassName}"
    new_file="${new_file//skeleton/$package_slug}"
    new_file="${new_file//laravel_/}"
    new_file="${new_file//laravel-/}"

    echo "adapting file $file -> $new_file"
        temp_file="$file.temp"
        < "$file" \
          sed "s#:project_slug#$project_slug#g" \
        | sed "s#:author_name#$author_name#g" \
        | sed "s#:author_username#$author_username#g" \
        | sed "s#author@domain.com#$author_email#g" \
        | sed "s#:project_name#$project_name#g" \
        | sed "s#:vendor_name#$vendor_name#g" \
        | sed "s#:VendorName#$VendorName#g" \
        | sed "s#vendor_slug#$vendor_slug#g" \
        | sed "s#:project_description#$project_description#g" \
        | sed "#^\[\]\(delete\) #d" \
        > "$temp_file"
        rm -f "$file"
        mv "$temp_file" "$new_file"
done

if confirm "Execute composer install and phpunit test"; then
    composer install && ./vendor/bin/phpunit
fi

if confirm 'Let this script delete itself (since you only need it once)?'; then
    echo "Delete $0 !"
    sleep 1 && rm -- "$0"
fi
