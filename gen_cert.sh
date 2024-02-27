#!/bin/bash
set -e
cd /etc/openvpn/

if [ $# -ne 3 ]; then
        echo "Usage: ./gen_cert.sh <dir> <username> <password>"
        exit 1
fi

cd $1

# Finds difference between current date and the next April 31st

export YEAR=$(date +%y)
export MONTH=$(date +%m)

if (( $MONTH > 4 )); then
        export YEAR=$(($YEAR + 1))
fi
export EASYRSA_CERT_EXPIRE=$(( ($(date --date "${YEAR}0501" +%s) - $(date +%s) ) /(60*60*24) ))
#export EASYRSA_CERT_EXPIRE=3650
echo "Cert will expire in $EASYRSA_CERT_EXPIRE days"

if [ ! -f "pki/private/$2.key" ]; then
        ./easyrsa gen-req --batch $2 nopass
fi
./easyrsa sign-req --batch --passin pass:$3 client $2

cp template.ovpn clients/$2.ovpn
cat pki/issued/$2.crt >> clients/$2.ovpn
echo "</cert>" >> clients/$2.ovpn
echo "<key>" >> clients/$2.ovpn
cat pki/private/$2.key >> clients/$2.ovpn
echo "</key>" >> clients/$2.ovpn

cat clients/$2.ovpn

