#!/bin/bash
set -u
DIR=public/assets/img/airlines
mkdir -p "$DIR"
AGENT='FaresHuntLogoCache/1.0 (https://fareshunt.com; info@fareshunt.com)'
ok=0; fail=0
while read -r code url; do
  [ -z "$code" ] && continue
  tmp="$DIR/$code.tmp"
  if curl -fsSL --max-time 30 -A "$AGENT" -o "$tmp" "$url" && head -c 600 "$tmp" | grep -qiE '<svg|<\?xml'; then
    mv "$tmp" "$DIR/$code.svg"; echo "OK   $code.svg"; ok=$((ok+1))
  else
    rm -f "$tmp"; echo "FAIL $code"; fail=$((fail+1))
  fi
  sleep 2
done <<'LIST'
AA https://commons.wikimedia.org/wiki/Special:Redirect/file/AA_Symbol.svg
DL https://commons.wikimedia.org/wiki/Special:Redirect/file/Delta_logo.svg
AC https://commons.wikimedia.org/wiki/Special:Redirect/file/Air_Canada_2017.svg
BA https://commons.wikimedia.org/wiki/Special:Redirect/file/BRITISH_AIRWAYS_logo.svg
EK https://commons.wikimedia.org/wiki/Special:Redirect/file/Emirates_Logo.svg
QR https://commons.wikimedia.org/wiki/Special:Redirect/file/Qatar_Airways_logo.svg
LH https://commons.wikimedia.org/wiki/Special:Redirect/file/Lufthansa_Logo_2018_crane.svg
AF https://commons.wikimedia.org/wiki/Special:Redirect/file/Air_France_Logo.svg
AI https://commons.wikimedia.org/wiki/Special:Redirect/file/Air_India_2023.svg
SQ https://commons.wikimedia.org/wiki/Special:Redirect/file/Singapore_Airlines_Logo.svg
NH https://commons.wikimedia.org/wiki/Special:Redirect/file/All_Nippon_Airways_Logo.svg
LIST
if curl -fsSL --max-time 30 -A "$AGENT" -o "$DIR/UA.tmp" 'https://www.google.com/s2/favicons?sz=128&domain_url=https://www.united.com' && head -c 8 "$DIR/UA.tmp" | grep -q PNG; then
  mv "$DIR/UA.tmp" "$DIR/UA.png"; echo "OK   UA.png"; ok=$((ok+1))
else rm -f "$DIR/UA.tmp"; echo "FAIL UA"; fail=$((fail+1)); fi
chmod 644 "$DIR"/* 2>/dev/null
echo "Done: $ok OK, $fail FAIL"
