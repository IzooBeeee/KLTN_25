#!/usr/bin/env python3
"""Test refinement engine for BDS chatbot."""

import json
import logging
from app_bds import BDSChatbot, ChatMemory

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


def test_refinement():
    """Test refinement flow: search -> cheaper -> larger -> near_beach."""
    chatbot = BDSChatbot()
    session_id = "test_refinement_session"

    print("\n" + "=" * 60)
    print("TEST 1: Initial search - căn hộ 5 tỷ")
    print("=" * 60)

    payload = {
        "session_id": session_id,
        "message": "tìm căn hộ Đà Nẵng khoảng 5 tỷ",
        "role": "guest",
    }
    result1 = chatbot.process(payload)
    print(f"Intent: {result1.get('intent')}")
    print(f"Suggestions count: {len(result1.get('suggestions', []))}")
    if result1.get("suggestions"):
        print(f"First suggestion: {result1['suggestions'][0].get('tieu_de', 'N/A')}")

    print("\n" + "=" * 60)
    print("TEST 2: Refinement - Rẻ hơn")
    print("=" * 60)

    payload = {"session_id": session_id, "message": "rẻ hơn", "role": "guest"}
    result2 = chatbot.process(payload)
    print(f"Intent: {result2.get('intent')}")
    print(f"Suggestions count: {len(result2.get('suggestions', []))}")
    if result2.get("suggestions"):
        print(f"First suggestion: {result2['suggestions'][0].get('tieu_de', 'N/A')}")

    print("\n" + "=" * 60)
    print("TEST 3: Refinement - Rộng hơn")
    print("=" * 60)

    payload = {"session_id": session_id, "message": "rộng hơn", "role": "guest"}
    result3 = chatbot.process(payload)
    print(f"Intent: {result3.get('intent')}")
    print(f"Suggestions count: {len(result3.get('suggestions', []))}")
    if result3.get("suggestions"):
        print(f"First suggestion: {result3['suggestions'][0].get('tieu_de', 'N/A')}")

    print("\n" + "=" * 60)
    print("TEST 4: Refinement - Gần biển")
    print("=" * 60)

    payload = {"session_id": session_id, "message": "gần biển", "role": "guest"}
    result4 = chatbot.process(payload)
    print(f"Intent: {result4.get('intent')}")
    print(f"Suggestions count: {len(result4.get('suggestions', []))}")
    if result4.get("suggestions"):
        print(f"First suggestion: {result4['suggestions'][0].get('tieu_de', 'N/A')}")

    print("\n" + "=" * 60)
    print("REFINEMENT ENGINE TEST COMPLETED")
    print("=" * 60)
    print(f"\nAll tests executed successfully.")
    print(f"Result 1 suggestions: {len(result1.get('suggestions', []))}")
    print(f"Result 2 suggestions: {len(result2.get('suggestions', []))}")
    print(f"Result 3 suggestions: {len(result3.get('suggestions', []))}")
    print(f"Result 4 suggestions: {len(result4.get('suggestions', []))}")


if __name__ == "__main__":
    test_refinement()
