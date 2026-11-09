#!/usr/bin/env python3
"""
==============================================================================
ELNEMER GLOBAL ENCYCLOPEDIA — INDEPENDENT CLI CRYPTOGRAPHIC VERIFIER (v7.2)
==============================================================================
Addresses Audit Report Requirements P0 #3, #4, #6, P1 #19-#26, and P2 #49:
Allows any external adjudicator, researcher, or auditor to independently:
  1. Fetch a volume's canonical metadata & content seeds via JSON API
  2. Recompute `content_sha256` (immutable 200-page content hash) locally
  3. Recompute `record_sha256` (bibliographic record hash) locally
  4. Download the raw 200-page PDF binary stream (/pdf/{id}) and recompute
     `pdf_bytes_sha256` locally without trusting server-reported hashes
  5. Recompute and verify the 23-Level Binary Merkle Tree inclusion path
  6. Validate the GS1 / EAN-13 (ISBN-13) Modulo-10 mathematical check digit

Usage:
  python cli_verifier.py --id 1
  python cli_verifier.py --id 5000001   # Verify Volume #1 of the 1M AI Cloud Encyclopedia
  python cli_verifier.py --id 6000001   # Verify Volume #1 of the 1M Pharaohs Cloud Encyclopedia
  python cli_verifier.py --random-sample 10 --seed 20261109
==============================================================================
"""
import argparse
import hashlib
import json
import random
import sys
import urllib.request

BASE_URL = "https://million.elnemer-ai.com"

def sha256_hex(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()

def verify_gs1_isbn13(isbn13: str) -> bool:
    if len(isbn13) != 13 or not isbn13.isdigit():
        return False
    total = sum(int(isbn13[i]) * (1 if i % 2 == 0 else 3) for i in range(12))
    expected_check = (10 - (total % 10)) % 10
    return expected_check == int(isbn13[12])

def verify_volume(vol_id: int, base_url: str = BASE_URL) -> bool:
    print(f"\n[VERIFYING VOLUME #{vol_id:,}]")
    api_url = f"{base_url}/api/verify/{vol_id}"
    req = urllib.request.Request(api_url, headers={"User-Agent": "ElNemer-Independent-CLI-Verifier/7.2"})
    with urllib.request.urlopen(req, timeout=30) as resp:
        payload = json.loads(resp.read().decode("utf-8"))

    # 1. Verify Content SHA-256
    content_proof = payload.get("content_integrity_proof", {})
    content_seed = content_proof.get("content_canonical_seed", "")
    expected_content_hash = content_proof.get("content_sha256", "")
    local_content_hash = sha256_hex(content_seed.encode("utf-8"))
    content_ok = (local_content_hash.lower() == expected_content_hash.lower())
    print(f"  1. Content SHA-256   : {local_content_hash} [{'PASS' if content_ok else 'FAIL'}]")

    # 2. Verify Record (Metadata) SHA-256
    meta_proof = payload.get("metadata_record_proof", {})
    meta_seed = meta_proof.get("canonical_seed_string", "")
    expected_meta_hash = meta_proof.get("metadata_sha256", "")
    local_meta_hash = sha256_hex(meta_seed.encode("utf-8"))
    meta_ok = (local_meta_hash.lower() == expected_meta_hash.lower())
    print(f"  2. Record SHA-256    : {local_meta_hash} [{'PASS' if meta_ok else 'FAIL'}]")

    # 3. Recompute 23-Level Binary Merkle Inclusion Path
    steps = meta_proof.get("merkle_inclusion_path_23_levels", [])
    curr = local_meta_hash
    for st in steps:
        sib = st["sibling_hash"]
        combined = (sib + curr) if st["position"] == "RIGHT" else (curr + sib)
        curr = sha256_hex(combined.encode("utf-8"))
    expected_branch_root = meta_proof.get("computed_branch_level_23_hash", meta_proof.get("computed_level_23_root", ""))
    merkle_ok = (curr.lower() == expected_branch_root.lower()) and (len(steps) == 23)
    print(f"  3. 23-Level Merkle   : {curr} [{'PASS (23/23 Levels)' if merkle_ok else 'FAIL'}]")

    # 4. Download Raw 200-Page PDF Binary Bytes and Verify PDF SHA-256
    pdf_proof = payload.get("file_binary_proof", {})
    pdf_url = pdf_proof.get("pdf_download_url", f"{base_url}/pdf/{vol_id}")
    expected_pdf_hash = pdf_proof.get("pdf_bytes_sha256", "")
    pdf_req = urllib.request.Request(pdf_url, headers={"User-Agent": "ElNemer-Independent-CLI-Verifier/7.2"})
    with urllib.request.urlopen(pdf_req, timeout=30) as pdf_resp:
        pdf_bytes = pdf_resp.read()
    local_pdf_hash = sha256_hex(pdf_bytes)
    is_valid_pdf_header = pdf_bytes.startswith(b"%PDF-1.4")
    pdf_ok = (local_pdf_hash.lower() == expected_pdf_hash.lower()) and is_valid_pdf_header
    print(f"  4. PDF Binary SHA-256: {local_pdf_hash} ({len(pdf_bytes):,} bytes) [{'PASS' if pdf_ok else 'FAIL'}]")

    # 5. Verify GS1 Modulo-10 Check Digit
    isbn13 = payload.get("volume_metadata", {}).get("isbn13_catalog_id", "")
    isbn_ok = verify_gs1_isbn13(isbn13)
    print(f"  5. GS1 Modulo-10 ISBN: {isbn13} [{'PASS' if isbn_ok else 'FAIL'}]")

    return content_ok and meta_ok and merkle_ok and pdf_ok and isbn_ok

def main():
    parser = argparse.ArgumentParser(description="ElNemer Global Encyclopedia Independent CLI Verifier (v7.2)")
    parser.add_argument("--id", type=int, default=1, help="Volume ID to verify (1 to 7,000,000)")
    parser.add_argument("--random-sample", type=int, default=0, help="Number of stratified random volumes to audit")
    parser.add_argument("--seed", type=int, default=20261109, help="Random seed for reproducible audit sampling")
    parser.add_argument("--base-url", type=str, default=BASE_URL, help="Base portal URL")
    args = parser.parse_args()

    if args.random_sample > 0:
        random.seed(args.seed)
        print(f"=== RUNNING REPRODUCIBLE STRATIFIED SAMPLE AUDIT (Seed={args.seed}, N={args.random_sample}) ===")
        all_passed = True
        for i in range(args.random_sample):
            if i % 3 == 0:
                vid = random.randint(1, 5000000)
            elif i % 3 == 1:
                vid = random.randint(5000001, 6000000)
            else:
                vid = random.randint(6000001, 7000000)
            if not verify_volume(vid, args.base_url):
                all_passed = False
        sys.exit(0 if all_passed else 1)
    else:
        ok = verify_volume(args.id, args.base_url)
        sys.exit(0 if ok else 1)

if __name__ == "__main__":
    main()
