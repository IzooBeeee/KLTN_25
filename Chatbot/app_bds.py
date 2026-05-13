import json
import logging
import os
import random
import re
import time
import traceback
import unicodedata

print("NEW APP_BDS VERSION 999 - FIXED")
from copy import deepcopy
from dataclasses import dataclass, field
from datetime import datetime, timedelta
from typing import Any, Dict, List, Optional, Tuple

# Gemini
import google.generativeai as genai

# Groq
from groq import Groq

import mysql.connector
from dotenv import load_dotenv
from flask import Flask, jsonify, request
from flask_cors import CORS

try:
    import google.generativeai as genai
except Exception:  # pragma: no cover
    genai = None

# Load .env.bds với absolute path
env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env.bds")
print(f"🔍 Loading .env from: {env_path}")
load_dotenv(env_path)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(levelname)s | %(name)s | %(message)s",
)
logger = logging.getLogger("bds-ai-assistant")

APP_VERSION = "2.0.0-production-style"
MAX_HISTORY = int(os.getenv("CHATBOT_MAX_HISTORY", "16"))
MAX_SESSIONS = int(os.getenv("CHATBOT_MAX_SESSIONS", "300"))
CONTEXT_TTL_SECONDS = int(os.getenv("CHATBOT_CONTEXT_TTL_SECONDS", "1800"))

DA_NANG_NEARBY_DISTRICTS = {
    "hải châu": ["thanh khê", "sơn trà", "ngũ hành sơn"],
    "hai chau": ["thanh khe", "son tra", "ngu hanh son"],
    "thanh khê": ["hải châu", "liên chiểu", "sơn trà"],
    "thanh khe": ["hai chau", "lien chieu", "son tra"],
    "sơn trà": ["hải châu", "ngũ hành sơn", "thanh khê"],
    "son tra": ["hai chau", "ngu hanh son", "thanh khe"],
    "ngũ hành sơn": ["sơn trà", "hải châu", "cẩm lệ"],
    "ngu hanh son": ["son tra", "hai chau", "cam le"],
    "cẩm lệ": ["hải châu", "ngũ hành sơn", "hòa vang"],
    "cam le": ["hai chau", "ngu hanh son", "hoa vang"],
    "liên chiểu": ["thanh khê", "hòa vang", "hải châu"],
    "lien chieu": ["thanh khe", "hoa vang", "hai chau"],
    "hòa vang": ["cẩm lệ", "liên chiểu", "ngũ hành sơn"],
    "hoa vang": ["cam le", "lien chieu", "ngu hanh son"],
    "đà nẵng": ["hải châu", "sơn trà", "ngũ hành sơn", "thanh khê"],
    "da nang": ["hai chau", "son tra", "ngu hanh son", "thanh khe"],
}


def now_ts() -> float:
    return time.time()


def normalize_text(text: str) -> str:
    text = (text or "").strip().lower()
    replacements = {
        "òa": "oà",
        "óa": "oá",
        "ỏa": "oả",
        "õa": "oã",
        "ọa": "oạ",
        "ùy": "uỳ",
        "úy": "uý",
        "ủy": "uỷ",
        "ũy": "uỹ",
        "ụy": "uỵ",
    }
    for src, dst in replacements.items():
        text = text.replace(src, dst)
    return re.sub(r"\s+", " ", text)


def normalize_ascii(text: str) -> str:
    text = normalize_text(text).replace("đ", "d")
    text = unicodedata.normalize("NFKD", text)
    text = "".join(ch for ch in text if not unicodedata.combining(ch))
    return re.sub(r"\s+", " ", text).strip()


def safe_int(value: Any, default: Optional[int] = None) -> Optional[int]:
    try:
        if value is None or value == "":
            return default
        return int(float(value))
    except Exception:
        return default


def money_vnd(value: Any) -> str:
    amount = safe_int(value, 0) or 0
    if amount >= 1_000_000_000:
        num = amount / 1_000_000_000
        return f"{num:.1f}".replace(".0", "") + " tỷ"
    if amount >= 1_000_000:
        num = amount / 1_000_000
        return f"{num:.0f} triệu"
    return f"{amount:,}đ".replace(",", ".")


def parse_price(text: str) -> Tuple[Optional[int], Optional[int]]:
    t = normalize_text(text)
    values = []
    price_pattern = re.compile(
        r"(\d+(?:[\.,]\d+)?)\s*(tỷ|ty|tỉ|triệu|trieu|tr|k|m)",
        re.IGNORECASE,
    )
    for match in price_pattern.finditer(t):
        raw, unit = match.group(1), match.group(2)
        suffix = t[match.end() : match.end() + 12]
        if unit in ["tr", "k", "m"] and suffix[:1].isalpha():
            continue
        if unit == "m":
            suffix = suffix.lstrip()
            if suffix.startswith(("2", "²", "et", "ét")):
                continue
        n = float(raw.replace(",", "."))
        if unit in ["tỷ", "ty", "tỉ"]:
            values.append(int(n * 1_000_000_000))
        elif unit in ["triệu", "trieu", "tr", "m"]:
            values.append(int(n * 1_000_000))
        elif unit == "k":
            values.append(int(n * 1000))
    if not values:
        return None, None
    if any(k in t for k in ["dưới", "duoi", "tối đa", "không quá", "<=", "ít hơn"]):
        return None, max(values)
    if any(k in t for k in ["trên", "hon", "hơn", "từ", ">="]):
        if len(values) == 1:
            return min(values), None
    if len(values) >= 2:
        return min(values), max(values)
    v = values[0]
    if any(k in t for k in ["khoảng", "tam", "tầm", "cỡ"]):
        return int(v * 0.70), int(v * 1.30)
    return None, v


def parse_area(text: str) -> Tuple[Optional[float], Optional[float]]:
    t = normalize_text(text)
    nums = [
        float(x.replace(",", "."))
        for x in re.findall(r"(\d+(?:[\.,]\d+)?)\s*(?:m2|m²|mét vuông|met vuong)", t)
    ]
    if not nums:
        return None, None
    if any(k in t for k in ["dưới", "duoi", "nhỏ hơn", "không quá"]):
        return None, max(nums)
    if any(k in t for k in ["trên", "hơn", "rộng hơn", "từ"]):
        return min(nums), None
    if len(nums) >= 2:
        return min(nums), max(nums)
    return int(nums[0] * 0.85), int(nums[0] * 1.2)


@dataclass
class ChatMemory:
    history: List[Dict[str, str]] = field(default_factory=list)
    active_domain: str = "general"
    active_task: Optional[str] = None
    pending_followup: Optional[str] = None
    refinement_mode: Optional[str] = None
    refinement_enabled: bool = False
    recommendation_context: Dict[str, Any] = field(default_factory=dict)
    current_property_type: Optional[str] = None
    current_budget: Dict[str, Any] = field(default_factory=dict)
    current_location: Optional[str] = None
    active_context: Optional[str] = None
    last_intent: Optional[str] = None
    entities: Dict[str, Any] = field(default_factory=dict)
    last_results: List[Dict[str, Any]] = field(default_factory=list)
    selected_property: Optional[Dict[str, Any]] = None
    last_booked_property: Optional[Dict[str, Any]] = None
    last_search_filters: Dict[str, Any] = field(default_factory=dict)
    booking_state: Dict[str, Any] = field(default_factory=dict)
    last_package_action: Optional[str] = None  # list_packages | buy_guide | posting_guide
    last_no_result_filters: Dict[str, Any] = field(default_factory=dict)  # filters that returned 0 results
    no_result_context: Dict[str, Any] = field(default_factory=lambda: {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0})
    valuation_state: Dict[str, Any] = field(default_factory=lambda: {"status": "idle", "entities": {}})
    last_property_detail_at: Optional[float] = None
    last_property_context_type: Optional[str] = None
    pending_followup: Optional[str] = None  # e.g. "similar_type:studio"
    actor: str = "guest"  # customer | broker | admin | guest
    ended: bool = False
    updated_at: float = field(default_factory=now_ts)


# ---------------------------------------------------------------------------
# Role helpers
# ---------------------------------------------------------------------------

def normalize_actor(payload: Dict[str, Any]) -> str:
    """Normalize role/actor from payload to canonical actor string."""
    raw = (
        payload.get("actor")
        or payload.get("role")
        or payload.get("user_type")
        or "guest"
    ).lower().strip()
    if raw in {"khach_hang", "customer", "khach", "user", "khach-hang"}:
        return "customer"
    if raw in {"moi_gioi", "broker", "agent", "seller", "moi-gioi"}:
        return "broker"
    if raw == "admin":
        return "admin"
    return "guest"


class RolePolicy:
    """Enforces role-based intent routing.
    Returns a ready-made wrap dict if the intent is disallowed, else None.
    """

    CUSTOMER_ONLY: set = {
        "search_property",
        "refine_search",
        "property_detail",
        "appointment",
        "valuation",
        "become_broker_guide",
        "general_customer_help",
    }

    BROKER_ONLY: set = {
        "package_buy_guide",
        "posting_guide",
        "broker_listing_guide",
        "broker_package_status",
        "broker_appointments",
        "broker_leads",
        "broker_profile_help",
        "general_broker_help",
    }

    @staticmethod
    def _customer_redirect(intent: str) -> Dict[str, Any]:
        """Response when customer asks broker-only feature."""
        # Use python dicts properly. Ensure encoding is UTF-8 handled.
        res = ""
        if intent == "package_buy_guide":
            res = "Việc mua gói tin dành cho tài khoản môi giới. Nếu bạn muốn đăng BĐS trên hệ thống, trước tiên bạn cần đăng ký/chuyển sang tài khoản môi giới.<br><br>"
        elif intent == "posting_guide":
            res = "Đăng bài BĐS là chức năng dành cho tài khoản môi giới. Bạn cần đăng ký/chuyển sang môi giới, sau đó mới có thể mua gói và đăng tin.<br><br>"
        elif intent == "become_broker_guide":
            res = ""
        else:
            res = "Phần này dành cho tài khoản môi giới. Nếu bạn muốn đăng BĐS, mình có thể hướng dẫn cách trở thành môi giới trên hệ thống.<br><br>"
        
        # Then append the "Cách trở thành môi giới" flow
        flow = (
            "<b>Cách trở thành môi giới:</b><br>"
            "1. Đăng xuất tài khoản khách hàng (nếu đang đăng nhập).<br>"
            "2. Truy cập vào mục Đăng ký ở góc phải trên, chọn Đăng ký môi giới (hoặc vào link /moi-gioi/dang-ky).<br>"
            "3. Điền đầy đủ thông tin cá nhân.<br>"
            "4. Đăng nhập tài khoản môi giới vừa tạo.<br>"
            "Sau khi có tài khoản môi giới, bạn có thể mua gói tin và đăng bài BĐS trên hệ thống."
        )
        res += flow

        return {
            "success": True,
            "response": res,
            "intent": "broker_feature_redirect",
            "context": "customer",
            "suggestions": [],
            "quick_replies": ["Xem bảng giá gói tin", "Tìm BĐS", "Định giá BĐS"],
        }

    @staticmethod
    def _broker_redirect(intent: str) -> Dict[str, Any]:
        """Response when broker asks customer-only feature."""
        return {
            "success": True,
            "response": (
                "Chế độ này dành cho môi giới nên mình ưu tiên hỗ trợ gói tin, đăng bài và lịch khách hẹn. "
                "Nếu bạn muốn tìm BĐS như khách hàng, hãy chuyển sang khu khách hàng."
            ),
            "intent": "customer_feature_redirect",
            "context": "broker",
            "suggestions": [],
            "quick_replies": ["Gói tin đăng BĐS", "Hướng dẫn đăng bài", "Lịch khách hẹn xem nhà"],
        }

    @classmethod
    def enforce(
        cls, actor: str, analysis: Dict[str, Any], mem: "ChatMemory"
    ) -> Optional[Dict[str, Any]]:
        """Return redirect response if intent violates role policy, else None."""
        intent = analysis.get("intent") or ""
        if actor == "customer" and intent in cls.BROKER_ONLY:
            return cls._customer_redirect(intent)
        if actor == "broker" and intent in cls.CUSTOMER_ONLY:
            return cls._broker_redirect(intent)
        return None


class MemoryManager:
    def __init__(self):
        self.sessions: Dict[str, ChatMemory] = {}

    def get(self, session_id: str) -> ChatMemory:
        self.cleanup()
        if session_id not in self.sessions:
            if len(self.sessions) >= MAX_SESSIONS:
                oldest = min(
                    self.sessions.items(), key=lambda item: item[1].updated_at
                )[0]
                logger.info(
                    "context_release | reason=max_sessions | session=%s", oldest
                )
                self.sessions.pop(oldest, None)
            self.sessions[session_id] = ChatMemory()
        return self.sessions[session_id]

    def cleanup(self):
        cutoff = now_ts() - CONTEXT_TTL_SECONDS
        for sid in list(self.sessions.keys()):
            if self.sessions[sid].updated_at < cutoff:
                logger.info("context_release | reason=ttl | session=%s", sid)
                self.sessions.pop(sid, None)

    def reset(self, session_id: str, reason: str):
        if session_id in self.sessions:
            logger.info("context_reset | reason=%s | session=%s", reason, session_id)
            self.sessions[session_id] = ChatMemory()

    def update(
        self,
        session_id: str,
        user_msg: str,
        bot_msg: str,
        analysis: Dict[str, Any],
        results: List[Dict[str, Any]],
    ):
        mem = self.get(session_id)
        results = results or []
        intent = analysis.get("intent")
        context = analysis.get("context")
        entities = analysis.get("entities") or {}
        resolved_property = analysis.get("selected_property") or analysis.get(
            "resolved_property"
        )
        is_property_followup = bool(
            resolved_property
            or analysis.get("property_reference_resolved")
            or analysis.get("booking_flow")
            or entities.get("resolved_property_id")
        )
        is_search_success = bool(results) and intent in [
            "search_property",
            "refine_search",
        ]
        is_search_intent = intent in [
            "search_property",
            "refine_search",
            "switch_property_type",
        ]
        is_booking_flow = bool(analysis.get("booking_flow"))

        mem.history.extend(
            [
                {"role": "user", "content": user_msg[:1000]},
                {"role": "assistant", "content": bot_msg[:1200]},
            ]
        )
        mem.history = mem.history[-MAX_HISTORY:]
        mem.last_intent = intent

        # context isolation: package intents do NOT override property/booking context
        is_package_intent = intent in ["package_info", "package_buy_guide", "posting_guide"]
        if is_search_success:
            is_currently_booking = mem.active_context == "booking" or is_booking_flow
            mem.active_context = "search"
            mem.active_domain = "property"
            mem.refinement_enabled = True
            mem.last_search_filters = deepcopy(entities)
            if not is_currently_booking:
                mem.booking_state = {}
        elif is_search_intent and not is_booking_flow:
            mem.active_context = "search"
            mem.active_domain = "property"
            mem.refinement_enabled = True
            if entities:
                mem.last_search_filters = deepcopy(entities)
        elif is_booking_flow:
            mem.active_context = "booking"
            mem.active_domain = "property"
        elif is_property_followup:
            mem.active_context = mem.active_context or "search"
            mem.active_domain = "property"
        elif is_package_intent:
            # Package intents keep property context intact — just update last_package_action
            mem.last_package_action = (
                "list_packages" if intent == "package_info"
                else "buy_guide" if intent == "package_buy_guide"
                else "posting_guide"
            )
            # active_context stays as-is so next property reference still works
        else:
            mem.active_context = context
            mem.active_domain = context or mem.active_domain

        mem.active_task = intent
        if is_property_followup and mem.entities:
            merged_entities = deepcopy(mem.entities)
            merged_entities.update(
                {
                    k: v
                    for k, v in entities.items()
                    if k.startswith("resolved_") and v not in [None, "", []]
                }
            )
            mem.entities = merged_entities
        else:
            mem.entities = entities

        if not is_property_followup or is_search_success:
            mem.current_property_type = (
                entities.get("property_type")
                if isinstance(entities.get("property_type"), str)
                else (
                    entities.get("property_type")[0]
                    if isinstance(entities.get("property_type"), list)
                    and entities.get("property_type")
                    else mem.current_property_type
                )
            )
            mem.current_location = entities.get("location") or (
                entities.get("locations")[0]
                if isinstance(entities.get("locations"), list)
                and entities.get("locations")
                else mem.current_location
            )
            mem.current_budget = {
                "price_min": entities.get("price_min"),
                "price_max": entities.get("price_max"),
                "area_min": entities.get("area_min"),
                "area_max": entities.get("area_max"),
            }

        mem.refinement_mode = analysis.get("refinement_mode")
        mem.pending_followup = analysis.get("pending_followup")
        if not is_property_followup or is_search_success:
            mem.recommendation_context = analysis.get("recommendation_context") or {
                "property_type": mem.current_property_type,
                "location": mem.current_location,
                "budget": mem.current_budget,
            }
        if mem.active_context == "booking" or (mem.booking_state and mem.booking_state.get("status") == "collecting"):
            logger.info("booking_state_before=%s", mem.booking_state)
            logger.info("active_context=%s intent=%s", mem.active_context, analysis.get("intent"))

        if analysis.get("intent") in ["appointment", "booking_flow"] and resolved_property:
            mem.selected_property = deepcopy(resolved_property)

        if "booking_state" in analysis:
            mem.booking_state = deepcopy(analysis["booking_state"])

        if is_search_success:
            mem.last_results = deepcopy(results[:10])
            mem.selected_property = deepcopy(mem.last_results[0])
            mem.last_search_filters = deepcopy(entities)
        elif resolved_property:
            # Only update selected_property for non-package intents so switching to
            # package guide does NOT wipe the property reference
            if not is_package_intent:
                mem.selected_property = deepcopy(resolved_property)

        mem.updated_at = now_ts()
        mem.ended = intent == "ending"


def resolve_property_reference(
    message: str, memory: ChatMemory
) -> Optional[Dict[str, Any]]:
    text = normalize_ascii(message)
    results = [item for item in (memory.last_results or []) if isinstance(item, dict)]
    selected = (
        memory.selected_property if isinstance(memory.selected_property, dict) else None
    )
    last_booked = (
        getattr(memory, 'last_booked_property', None) if isinstance(getattr(memory, 'last_booked_property', None), dict) else None
    )
    if not results and not selected and not last_booked:
        return None
    property_terms = r"(?:can|nha|bds|bat dong san|tin|listing)"
    ordinal_words = {
        "nhat": 1,
        "mot": 1,
        "hai": 2,
        "ba": 3,
        "bon": 4,
        "tu": 4,
        "nam": 5,
        "sau": 6,
        "bay": 7,
        "tam": 8,
        "chin": 9,
        "muoi": 10,
    }

    def pick_by_position(position: int) -> Optional[Dict[str, Any]]:
        return results[position - 1] if 1 <= position <= len(results) else None

    if re.search(rf"\b{property_terms}\s+(?:dau tien|dau|thu nhat|so 1|1)\b", text) or (len(text) <= 20 and re.search(r"\b(?:dau tien|dau|thu nhat|so 1|^1$)\b", text)):
        return pick_by_position(1)
    if re.search(rf"\b{property_terms}\s+cuoi\b", text) or (len(text) <= 20 and re.search(r"\bcuoi\b", text)):
        return results[-1] if results else selected
    numbered = re.search(
        rf"\b{property_terms}\s+(?:thu|so)?\s*(\d+)\b", text
    ) or re.search(r"\b(?:thu|so)\s*(\d+)\b", text) or (len(text) <= 20 and re.search(r"^(\d+)$", text))
    if numbered:
        return pick_by_position(int(numbered.group(1)))
    ordinal_words = {"nhat": 1, "hai": 2, "ba": 3, "tu": 4, "nam": 5, "sau": 6, "bay": 7, "tam": 8, "chin": 9, "muoi": 10}
    worded = re.search(rf"\b{property_terms}\s+thu\s+([a-z]+)\b", text) or (len(text) <= 20 and re.search(r"\bthu\s+([a-z]+)\b", text))
    if worded:
        return pick_by_position(ordinal_words.get(worded.group(1), 0))
    pointer_phrases = [
        "can do",
        "can nay",
        "nha do",
        "nha nay",
        "bds do",
        "bds nay",
        "bat dong san do",
        "bat dong san nay",
        "tin do",
        "tin nay",
        "listing do",
        "listing nay",
        "can vua roi",
        "nha vua roi",
        "bds vua roi",
        "can tren",
        "nha tren",
        "bds tren",
    ]
    if any(phrase in text for phrase in pointer_phrases):
        return selected or last_booked or (results[0] if results else None)
    return None


def new_booking_state(item: Dict[str, Any]) -> Dict[str, Any]:
    return {
        "property_id": item.get("id"),
        "property_title": item.get("tieu_de"),
        "date": None,
        "time": None,
        "status": "collecting",
    }


def upcoming_saturday(from_date=None):
    base = from_date or datetime.now().date()
    days_ahead = (5 - base.weekday()) % 7
    return base if days_ahead == 0 else base + timedelta(days=days_ahead)


def parse_booking_date(message: str) -> Dict[str, Any]:
    text = normalize_ascii(message)
    today = datetime.now().date()
    preferred_period = "evening" if any(k in text for k in ["toi", "dem"]) else None
    direct = re.search(r"\b(\d{1,2})[/-](\d{1,2})(?:[/-](\d{2,4}))?\b", text)
    if direct:
        day, month, year_raw = (
            int(direct.group(1)),
            int(direct.group(2)),
            direct.group(3),
        )
        year = (
            today.year
            if not year_raw
            else (int(year_raw) + 2000 if int(year_raw) < 100 else int(year_raw))
        )
        try:
            chosen = datetime(year, month, day).date()
            if chosen < today and not year_raw:
                chosen = datetime(today.year + 1, month, day).date()
            return {
                "date": chosen.isoformat(),
                "date_label": chosen.strftime("%d/%m/%Y"),
                "preferred_period": preferred_period,
            }
        except ValueError:
            return {}
    if "hom nay" in text:
        return {
            "date": today.isoformat(),
            "date_label": "hôm nay",
            "preferred_period": preferred_period,
        }
    if "mai" in text:
        chosen = today + timedelta(days=1)
        return {
            "date": chosen.isoformat(),
            "date_label": "tối mai" if preferred_period == "evening" else "ngày mai",
            "preferred_period": preferred_period,
        }
    if "cuoi tuan" in text:
        chosen = upcoming_saturday(today)
        return {
            "date": chosen.isoformat(),
            "date_label": f"cuối tuần ({chosen.strftime('%d/%m')})",
            "preferred_period": preferred_period,
        }
    if re.search(r"\b(?:thu\s*(?:7|bay)|t7)\b", text):
        chosen = upcoming_saturday(today)
        return {
            "date": chosen.isoformat(),
            "date_label": f"thứ 7 ({chosen.strftime('%d/%m')})",
            "preferred_period": preferred_period,
        }
    return {"preferred_period": preferred_period} if preferred_period else {}


def parse_booking_time(message: str, booking_state: Dict[str, Any]) -> Optional[str]:
    text = normalize_ascii(message)
    match = re.search(r"\b([01]?\d|2[0-3])\s*(?:h|gio|:)\s*(\d{1,2})?\b", text)
    if not match:
        return None
    hour, minute = int(match.group(1)), int(match.group(2) or 0)
    if minute > 59:
        return None
    period = None
    has_sang, has_afternoon, has_evening = (
        "sang" in text,
        any(k in text for k in ["chieu", "trua"]),
        any(k in text for k in ["toi", "dem"]),
    )
    if "pm" in text:
        period = "pm"
    elif "am" in text:
        period = "am"
    elif has_sang and not has_afternoon and not has_evening:
        period = "am"
    elif has_afternoon and not has_evening:
        period = "pm"
    elif has_evening:
        period = "pm"
    elif booking_state.get("preferred_period") == "evening":
        period = "pm"
    if 1 <= hour <= 12 and period:
        if period == "am" and hour == 12:
            hour = 0
        elif period == "pm" and hour != 12:
            hour += 12
    return f"{hour:02d}:{minute:02d}"


def booking_missing_field(booking_state: Dict[str, Any]) -> Optional[str]:
    for field_name in ["date", "time"]:
        if not booking_state.get(field_name):
            return field_name
    return None


def is_booking_cancel_intent(message: str) -> bool:
    text = normalize_ascii(message)
    cancel_patterns = [
        r"\bhuy\b",
        r"\bthoi\b",
        r"\bkhong\s+can\b",
        r"\bkhong\s+nen\b",
        r"\bkhong\s+nua\b",
        r"\bdung\b",
        r"\bbo\s+qua\b",
        r"\bkhoi\b",
        r"\bok\s+thoi\b",
        r"\btam\s+thoi\b",
    ]
    return any(re.search(pat, text) for pat in cancel_patterns)


def booking_datetime_is_future(booking_state: Dict[str, Any]) -> bool:
    try:
        booking_at = datetime.strptime(
            f"{booking_state.get('date')} {booking_state.get('time')}", "%Y-%m-%d %H:%M"
        )
        return booking_at > datetime.now()
    except Exception:
        return False


class GeminiPipeline:
    def __init__(self):
        self.enabled = False
        self.model = None
        self.groq_client = None
        api_key = os.getenv("GOOGLE_API_KEY")
        if api_key and genai:
            try:
                genai.configure(api_key=api_key)
                self.model = genai.GenerativeModel(
                    os.getenv("GEMINI_MODEL", "gemini-2.0-flash")
                )
                self.enabled = True
            except Exception as exc:
                logger.warning("gemini_init_fail | %s", exc)
        groq_key = os.getenv("GROQ_API_KEY")
        if groq_key:
            try:
                self.groq_client = Groq(api_key=groq_key)
                logger.info("groq_ready")
            except Exception as exc:
                logger.warning("groq_init_fail | %s", exc)

    def analyze(self, message: str, memory: ChatMemory) -> Optional[Dict[str, Any]]:
        if not self.enabled and not self.groq_client:
            return None
        prompt = f"""Bạn là NLP router cho trợ lý bất động sản Việt Nam. Trả JSON thuần. Intent: search_property, refine_search, package_info, valuation, appointment, property_detail, general_bds, ending, out_of_domain. Entities: location, property_type, price_min, price_max, area_min, area_max, bedrooms, bathrooms, purpose, lifestyle[], superlative. User: {message}. Context: {json.dumps(memory.entities, ensure_ascii=False)}"""
        if self.enabled and self.model:
            try:
                raw = self.model.generate_content(prompt).text or ""
                match = re.search(r"\{.*\}", raw, re.S)
                return json.loads(match.group(0) if match else raw)
            except Exception as exc:
                logger.warning("fallback_reason | gemini_analysis_fail | %s", exc)
        if self.groq_client:
            try:
                response = self.groq_client.chat.completions.create(
                    model="llama3-8b-8192",
                    messages=[{"role": "user", "content": prompt}],
                    temperature=0.2,
                )
                raw = response.choices[0].message.content or ""
                match = re.search(r"\{.*\}", raw, re.S)
                return json.loads(match.group(0) if match else raw)
            except Exception as groq_exc:
                logger.warning("fallback_reason | groq_analysis_fail | %s", groq_exc)
        return None

    def enhance(self, draft: str, message: str, analysis: Dict[str, Any]) -> str:
        if not self.enabled or len(draft) > 3500:
            return draft
        prompt = f"Viết lại câu trả lời chatbot BĐS bằng tiếng Việt tự nhiên. User: {message}. Intent: {analysis.get('intent')}. Draft:\n{draft}"
        try:
            raw = self.model.generate_content(prompt).text or draft
            return raw.strip() or draft
        except Exception:
            return draft


class RuleBasedNLU:
    PROPERTY_TYPES = {
        "căn hộ": "căn hộ",
        "chung cư": "căn hộ",
        "apartment": "căn hộ",
        "nhà phố": "nhà phố",
        "nhà riêng": "nhà phố",
        "nhà": "nhà phố",
        "biệt thự": "biệt thự",
        "villa": "biệt thự",
        "đất": "đất nền",
        "đất nền": "đất nền",
        "mặt bằng": "mặt bằng",
        "phòng trọ": "phòng trọ",
    }
    LIFESTYLE = {
        "hiện đại": "modern",
        "sang": "luxury",
        "cao cấp": "luxury",
        "luxury": "luxury",
        "view đẹp": "view",
        "view dep": "view",
        "view biển": "sea_view",
        "gần biển": "near_beach",
        "biển": "near_beach",
        "trung tâm": "center",
        "đầu tư": "investment",
        "gia đình": "family",
        "yên tĩnh": "quiet",
        "chill": "chill",
        "nhiều kính": "glass",
        "thoáng": "airy",
    }

    def extract_listing_count(self, text: str) -> Optional[int]:
        normalized = normalize_text(text)
        normalized_ascii = normalize_ascii(text)
        match = re.search(r"\b(\d+)\s*(?:tin đăng|tin)\b", normalized)
        if not match:
            match = re.search(r"\b(?:đăng|dang)\s+(\d+)\b", normalized_ascii)
        if match:
            return safe_int(match.group(1))
        # FIX 1: Check both diacritics and non-diacritics
        if any(
            k in normalized or k in normalized_ascii for k in ["mười", "muoi", "10"]
        ):
            return 10
        return None

    def analyze(self, message: str, memory: ChatMemory) -> Dict[str, Any]:
        t = normalize_text(message)
        t_ascii = normalize_ascii(message)  # FIX 1: Support non-diacritics
        entities: Dict[str, Any] = {}
        intent, context = "general_bds", "general"
        if not t:
            return {
                "intent": "empty",
                "context": None,
                "entities": {},
                "confidence": 1,
                "raw": t,
            }

        # SEARCH LIFESTYLE EARLY DETECTION: "Gợi ý nhà gần biển, view đẹp"
        search_lifestyle_kw = [
            "gợi ý nhà", "goi y nha", "gợi ý bđs", "goi y bds",
            "tìm nhà gần biển", "tim nha gan bien", "nhà gần biển", "nha gan bien",
            "bđs gần biển", "bds gan bien", "gần biển view", "gan bien view",
        ]
        if any(k in t or k in t_ascii for k in search_lifestyle_kw):
            intent, context = "search_property", "search"
            lifestyles = []
            if "gần biển" in t or "gan bien" in t_ascii:
                lifestyles.append("near_beach")
            if "view đẹp" in t or "view dep" in t_ascii or "view" in t:
                lifestyles.append("view")
            if lifestyles:
                entities["lifestyle"] = lifestyles
            if not entities.get("locations"):
                entities["locations"] = ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê"]

        # FIX 1: Check package keywords with both diacritics and non-diacritics
        package_keywords = [
            "gói vip",
            "goi vip",
            "gói tin",
            "goi tin",
            "xem gói",
            "xem goi",
            "package",
            "đăng 10 tin",
            "dang 10 tin",
            "bao nhiêu tin",
            "bao nhieu tin",
            "mấy tin",
            "may tin",
        ]
        if any(k in t or k in t_ascii for k in package_keywords):
            intent, context = "package_info", "package"
            
        # Package Buy Guide — must be checked AFTER package_info_exact so 'xem lại gói tin' is not captured here
        package_buy_keywords = [
            "cách mua gói tin", "cach mua goi tin",
            "mua gói tin", "mua goi tin",
            "mua gói", "mua goi",
            "thanh toán gói", "thanh toan goi",
            "làm sao mua gói", "lam sao mua goi",
            "hướng dẫn mua gói", "huong dan mua goi",
        ]
        if any(k in t or k in t_ascii for k in package_buy_keywords):
            intent, context = "package_buy_guide", "package"

        # Become Broker Guide
        broker_guide_keywords = [
            "cách trở thành môi giới", "cach tro thanh moi gioi",
            "làm sao trở thành môi giới", "lam sao tro thanh moi gioi",
            "tôi muốn làm môi giới", "toi muon lam moi gioi",
            "đăng ký môi giới", "dang ky moi gioi",
            "tôi muốn đăng bđs", "toi muon dang bds",
            "tôi muốn bán nhà trên hệ thống", "toi muon ban nha tren he thong",
            "muốn đăng tin thì làm sao", "muon dang tin thi lam sao",
            "khách hàng muốn đăng tin", "khach hang muon dang tin",
            "mua gói để đăng tin", "mua goi de dang tin",
        ]
        if any(k in t or k in t_ascii for k in broker_guide_keywords):
            intent, context = "become_broker_guide", "broker"

        # Package list (view/re-view) — takes priority OVER buy_guide for 'xem lại gói tin'
        package_view_keywords = [
            "xem lại gói tin", "xem lai goi tin",
            "xem gói tin", "xem goi tin",
            "bảng giá gói tin", "bang gia goi tin",
            "gói tin đăng bđs giá bao nhiêu", "goi tin dang bds gia bao nhieu",
        ]
        if any(k in t or k in t_ascii for k in package_view_keywords):
            intent, context = "package_info", "package"
            
        posting_keywords = [
            "hướng dẫn đăng tin", "huong dan dang tin",
            "hướng dẫn đăng", "huong dan dang",
            "cách đăng tin", "cach dang tin",
            "cách đăng bài", "cach dang bai",
            "đăng tin như thế nào", "dang tin nhu the nao",
            "làm sao đăng tin", "lam sao dang tin",
            "tôi muốn đăng tin", "toi muon dang tin",
            "đăng bài bđs", "dang bai bds",
            "đăng tin bđs", "dang tin bds",
            "đăng bài", "dang bai",
            "hướng dẫn đăng bài", "huong dan dang bai",
        ]
        if any(k in t or k in t_ascii for k in posting_keywords):
            intent, context = "posting_guide", "posting"
            
        listing_count = self.extract_listing_count(t)
        if listing_count is not None:
            entities["requested_listing_count"] = listing_count

        for k, v in self.PROPERTY_TYPES.items():
            if k in t:
                entities["property_type"] = v
                if intent == "general_bds":
                    intent = "search_property"
                if context == "general":
                    context = "search"
                break
        pmin, pmax = parse_price(t)
        amin, amax = parse_area(t)
        if pmin:
            entities["price_min"] = pmin
        if pmax:
            entities["price_max"] = pmax
        if amin:
            entities["area_min"] = amin
        if amax:
            entities["area_max"] = amax
        bedrooms = re.search(r"(\d+)\s*(?:pn|phòng ngủ|phong ngu)", t)
        if bedrooms:
            entities["bedrooms"] = int(bedrooms.group(1))
        for loc in [
            "đà nẵng",
            "da nang",
            "sơn trà",
            "son tra",
            "hải châu",
            "hai chau",
            "ngũ hành sơn",
            "ngu hanh son",
            "liên chiểu",
            "lien chieu",
            "hòa vang",
            "hoa vang",
            "cẩm lệ",
            "cam le",
            "thanh khê",
            "thanh khe",
        ]:
            if loc in t:
                entities["location"] = loc
                break
        lifestyles = [v for k, v in self.LIFESTYLE.items() if k in t]
        if lifestyles:
            entities["lifestyle"] = list(dict.fromkeys(lifestyles))
        if any(k in t for k in ["định giá", "giá bao nhiêu"]):
            intent, context = "valuation", "valuation"
        elif any(k in t for k in ["đặt lịch", "xem nhà", "hẹn"]):
            intent, context = "appointment", "appointment"
        elif any(k in t for k in ["rẻ hơn", "rộng hơn", "gần biển", "gần trung tâm"]):
            if "gần biển" in t and memory.active_context not in ["search"] and not memory.last_search_filters:
                # No prior search context → treat as broad beach search
                intent, context = "search_property", "search"
                lifestyles = entities.get("lifestyle") or []
                if "near_beach" not in lifestyles:
                    lifestyles.append("near_beach")
                entities["lifestyle"] = lifestyles
                if not entities.get("locations"):
                    entities["locations"] = ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê"]
            else:
                intent, context = "refine_search", (
                    "search" if memory.active_context == "search" else "general"
                )
                entities["refinement"] = True
                if "rẻ hơn" in t:
                    entities["refinement_type"] = "cheaper"
                elif "rộng hơn" in t:
                    entities["refinement_type"] = "larger"
                elif "gần biển" in t:
                    entities["refinement_type"] = "near_beach"
                elif "gần trung tâm" in t:
                    entities["refinement_type"] = "center"
        return {
            "intent": intent,
            "context": context,
            "entities": entities,
            "confidence": 0.72,
            "raw": t,
        }


class BDSDataFetcher:
    def connect(self):
        db_host = os.getenv("BDS_DB_HOST", "127.0.0.1")
        db_port = int(os.getenv("BDS_DB_PORT", "3306"))
        db_user = os.getenv("BDS_DB_USER", "root")
        db_pass = os.getenv("BDS_DB_PASSWORD", "")
        db_name = os.getenv("BDS_DB_NAME", "be_bds_kltn_t6")
        print(
            f"🔍 DEBUG CONNECT: Host={db_host}, Port={db_port}, User={db_user}, Pass='{db_pass}', DB={db_name}"
        )
        try:
            conn = mysql.connector.connect(
                host=db_host,
                port=db_port,
                database=db_name,
                user=db_user,
                password=db_pass,
                connection_timeout=5,
            )
            print("✅ MYSQL CONNECTED SUCCESSFULLY")
            return conn
        except mysql.connector.Error as err:
            print(f"❌ MYSQL CONNECTION ERROR: {err}")
            raise err

    @staticmethod
    def is_dirty_listing(row: Dict[str, Any]) -> bool:
        title = normalize_text(str(row.get("tieu_de") or ""))
        desc = normalize_text(str(row.get("mo_ta") or ""))
        text = f"{title} {desc}"
        if any(k in text for k in ["hihi", "test", "abc", "fake", "demo"]):
            return True
        price = safe_int(row.get("gia"), 0) or 0
        area = float(row.get("dien_tich") or 0)
        prop_type = normalize_text(str(row.get("ten_loai") or ""))
        if price and price < 100_000_000:
            return True
        if area and area > 2000 and "căn hộ" in prop_type:
            return True
        if area and area <= 0:
            return True
        return False

    def fetch_properties(
        self, filters: Dict[str, Any], limit: int = 12
    ) -> List[Dict[str, Any]]:
        where = ["b.is_duyet = 1", "(b.status IS NULL OR b.status = 'published')"]
        params: List[Any] = []
        if filters.get("property_type"):
            type_values = (
                filters.get("property_type")
                if isinstance(filters.get("property_type"), list)
                else [filters.get("property_type")]
            )
            type_conditions = []
            for property_type in [v for v in type_values if v]:
                type_conditions.append("LOWER(l.ten_loai) LIKE %s")
                params.append(f"%{str(property_type).lower()}%")
            if type_conditions:
                where.append("(" + " OR ".join(type_conditions) + ")")
        if filters.get("location") or filters.get("locations"):
            location_values = filters.get("locations") or filters.get("location")
            if not isinstance(location_values, list):
                location_values = [location_values]
            location_conditions = []
            for location in [v for v in location_values if v]:
                loc = f"%{str(location).lower()}%"
                location_conditions.append(
                    "(LOWER(tt.ten) LIKE %s OR LOWER(qh.ten) LIKE %s OR LOWER(px.ten) LIKE %s OR LOWER(dc.dia_chi_chi_tiet) LIKE %s)"
                )
                params.extend([loc, loc, loc, loc])
            if location_conditions:
                where.append("(" + " OR ".join(location_conditions) + ")")
        for key, col, op in [
            ("price_min", "b.gia", ">="),
            ("price_max", "b.gia", "<="),
            ("area_min", "b.dien_tich", ">="),
            ("area_max", "b.dien_tich", "<="),
            ("bedrooms", "b.so_phong_ngu", ">="),
        ]:
            if filters.get(key) is not None:
                where.append(f"{col} {op} %s")
                params.append(filters[key])
        order = "b.is_noi_bat DESC, b.created_at DESC"
        sup = filters.get("superlative")
        if sup == "most_expensive":
            order = "b.gia DESC"
        elif sup == "cheapest":
            order = "b.gia ASC"
        elif sup == "largest":
            order = "b.dien_tich DESC"
        elif sup == "newest":
            order = "b.created_at DESC"
        elif sup in ["best", "most_luxury"]:
            order = "b.is_noi_bat DESC, b.gia DESC"
        sql = f"""SELECT b.id, b.tieu_de, b.mo_ta, b.gia, b.dien_tich, b.so_phong_ngu, b.so_phong_tam, b.is_noi_bat, b.created_at, l.ten_loai, tt.ten AS tinh, qh.ten AS quan, px.ten AS phuong, dc.dia_chi_chi_tiet, mg.ten AS moi_gioi, (SELECT url FROM hinh_anh_bat_dong_sans h WHERE h.bds_id=b.id ORDER BY h.is_anh_dai_dien DESC, h.thu_tu ASC, h.id ASC LIMIT 1) AS anh_dai_dien_url FROM bat_dong_sans b LEFT JOIN loai_bat_dong_sans l ON l.id=b.loai_id LEFT JOIN dia_chis dc ON dc.id=b.dia_chi_id LEFT JOIN tinh_thanhs tt ON tt.id=dc.tinh_id LEFT JOIN quan_huyens qh ON qh.id=dc.quan_id LEFT JOIN phuong_xas px ON px.id=dc.phuong_xa_id LEFT JOIN moi_giois mg ON mg.id=b.moi_gioi_id WHERE {' AND '.join(where)} ORDER BY {order} LIMIT %s"""
        params.append(limit)
        try:
            conn = self.connect()
            cur = conn.cursor(dictionary=True)
            cur.execute(sql, params)
            rows = cur.fetchall()
            cur.close()
            conn.close()
            formatted = [self._format_row(r) for r in rows]
            return [r for r in formatted if not self.is_dirty_listing(r)]
        except Exception as exc:
            logger.error("sql_fail | %s | %s", exc, traceback.format_exc())
            return []

    # FIX 4: Use try/finally to ensure connection is always closed
    def fetch_packages(self):
        print("FETCH PACKAGES START")
        conn = None
        cur = None
        try:
            print("========== PACKAGE DEBUG ==========")
            print("DB =", os.getenv("BDS_DB_NAME"))
            conn = self.connect()
            print("MYSQL CONNECTED")
            cur = conn.cursor(dictionary=True)
            cur.execute("""
                SELECT id, ten_goi, mo_ta, gia, so_ngay, so_luong_tin, gan_nhan_vip, uu_tien_hien_thi 
                FROM goi_tins 
                ORDER BY gia ASC LIMIT 8
            """)
            rows = cur.fetchall()

            # --- FIX: Ép kiểu dữ liệu để JSON sạch hơn ---
            clean_rows = []
            for row in rows:
                clean_row = row.copy()
                clean_row["type"] = "package"
                clean_row["title"] = clean_row.get("ten_goi")
                clean_row["is_vip"] = bool(clean_row.get("gan_nhan_vip"))
                clean_row["duration_text"] = f"{clean_row.get('so_ngay')} ngày"
                clean_row["listing_count_text"] = f"{clean_row.get('so_luong_tin')} tin"
                
                # Chuyển gia từ string/decimal sang int
                if "gia" in clean_row:
                    try:
                        clean_row["gia"] = int(float(clean_row["gia"]))
                        clean_row["price_text"] = money_vnd(clean_row["gia"])
                    except:
                        pass

                # Chuyển số lượng tin và ngày sang int
                if "so_luong_tin" in clean_row:
                    try:
                        clean_row["so_luong_tin"] = int(clean_row["so_luong_tin"])
                    except:
                        pass
                if "so_ngay" in clean_row:
                    try:
                        clean_row["so_ngay"] = int(clean_row["so_ngay"])
                    except:
                        pass

                clean_rows.append(clean_row)
            # -------------------------------------------

            print(f"ROWS COUNT: {len(clean_rows)}")
            return clean_rows

        except Exception as exc:
            print("PACKAGE ERROR =", exc)
            logger.warning("sql_fail | packages | %s", exc)
            return []
        finally:
            if cur:
                try:
                    cur.close()
                except:
                    pass
            if conn:
                try:
                    conn.close()
                except:
                    pass

    def list_types(self):
        try:
            conn = self.connect()
            cur = conn.cursor(dictionary=True)
            cur.execute(
                "SELECT id, ten_loai FROM loai_bat_dong_sans WHERE is_active=1 ORDER BY ten_loai"
            )
            rows = cur.fetchall()
            cur.close()
            conn.close()
            return rows
        except Exception as e:
            print("PACKAGE ERROR =", e)
            return []

    def _format_row(self, r: Dict[str, Any]) -> Dict[str, Any]:
        address = ", ".join(
            [
                x
                for x in [
                    r.get("dia_chi_chi_tiet"),
                    r.get("phuong"),
                    r.get("quan"),
                    r.get("tinh"),
                ]
                if x
            ]
        )
        return {
            "type": "property",
            "id": r.get("id"),
            "tieu_de": r.get("tieu_de"),
            "mo_ta": (r.get("mo_ta") or "")[:220],
            "gia": int(r.get("gia") or 0),
            "gia_text": money_vnd(r.get("gia")),
            "dien_tich": r.get("dien_tich"),
            "so_phong_ngu": r.get("so_phong_ngu"),
            "so_phong_tam": r.get("so_phong_tam"),
            "loai": r.get("ten_loai"),
            "dia_chi": address,
            "anh_dai_dien_url": r.get("anh_dai_dien_url"),
            "url": f"/khach-hang/chi-tiet-bat-dong-san/{r.get('id')}",
            "is_noi_bat": bool(r.get("is_noi_bat")),
            "created_at": str(r.get("created_at")) if r.get("created_at") else None,
        }


class PropertyRecommendationEngine:
    KEYWORDS = {
        "modern": ["hiện đại", "smart", "mới"],
        "luxury": ["cao cấp", "sang", "vip"],
        "view": ["view", "ban công"],
        "sea_view": ["biển", "view biển"],
        "near_beach": ["biển", "bãi tắm"],
        "center": ["trung tâm", "hải châu"],
        "investment": ["đầu tư", "sinh lời"],
        "family": ["gia đình", "trường", "bệnh viện"],
        "quiet": ["yên tĩnh", "riêng tư"],
        "chill": ["chill", "ban công"],
        "glass": ["kính", "ánh sáng"],
        "airy": ["thoáng", "rộng"],
    }

    def rank(
        self, items: List[Dict[str, Any]], entities: Dict[str, Any]
    ) -> List[Dict[str, Any]]:
        lifestyles = entities.get("lifestyle") or []
        ranked = []
        locked_type = normalize_text(
            str(
                entities.get("property_type_locked")
                or entities.get("property_type")
                or ""
            )
        )
        for item in items:
            score = 50.0
            text = normalize_text(
                " ".join(
                    [
                        str(item.get("tieu_de") or ""),
                        str(item.get("mo_ta") or ""),
                        str(item.get("dia_chi") or ""),
                        str(item.get("loai") or ""),
                    ]
                )
            )
            item_type = normalize_text(item.get("loai") or "")
            if item.get("is_noi_bat"):
                score += 1
            if locked_type and locked_type not in item_type:
                score -= 100
            entity_type = entities.get("property_type")
            type_values = (
                entity_type if isinstance(entity_type, list) else [entity_type]
            )
            if any(normalize_text(str(v)) in item_type for v in type_values if v):
                score += 16
            if entities.get("location") and normalize_text(
                str(entities["location"])
            ) in normalize_text(item.get("dia_chi") or ""):
                score += 14
            elif entities.get("locations"):
                score += 6
            if entities.get("price_max"):
                try:
                    price_gap = max(
                        0.0, float(item.get("gia") or 0) - float(entities["price_max"])
                    )
                    score -= min(
                        14.0, price_gap / max(float(entities["price_max"]), 1) * 10
                    )
                except:
                    pass
            for lf in lifestyles:
                hits = sum(1 for kw in self.KEYWORDS.get(lf, []) if kw in text)
                score += hits * 4
            item = deepcopy(item)
            item["recommendation_score"] = round(score, 2)
            item["reason"] = self.reason(item, entities)
            ranked.append(item)
        ranked = [x for x in ranked if x.get("recommendation_score", 0) > -50]
        superlative = entities.get("superlative")
        if superlative == "cheapest":
            return sorted(ranked, key=lambda x: float(x.get("gia") or float("inf")))
        elif superlative == "largest":
            return sorted(ranked, key=lambda x: float(x.get("dien_tich") or 0), reverse=True)
        return sorted(
            ranked, key=lambda x: x.get("recommendation_score", 0), reverse=True
        )

    def reason(self, item: Dict[str, Any], entities: Dict[str, Any]) -> str:
        reasons = []
        item_type = normalize_text(item.get("loai") or "")
        item_address = normalize_text(item.get("dia_chi") or "")
        entity_type = entities.get("property_type")
        type_values = entity_type if isinstance(entity_type, list) else [entity_type]
        if any(normalize_text(str(v)) in item_type for v in type_values if v):
            reasons.append("đúng loại BĐS")
        if (
            entities.get("location")
            and normalize_text(str(entities.get("location"))) in item_address
        ):
            reasons.append("đúng khu vực")
        elif entities.get("locations"):
            reasons.append("khu vực lân cận")
        if entities.get("price_max"):
            reasons.append("gần ngân sách")
        if entities.get("lifestyle"):
            lifestyles = []
            for lf in entities.get("lifestyle")[:2]:
                if lf == "near_beach":
                    text_str = " ".join([str(item.get("tieu_de") or ""), str(item.get("mo_ta") or ""), str(item.get("dia_chi") or "")]).lower()
                    if any(kw in text_str for kw in ["biển", "bãi tắm", "sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê", "vỗ nguyên giáp", "phạm văn đồng", "an hải"]):
                        lifestyles.append("gần biển")
                elif lf == "center": lifestyles.append("gần trung tâm")
                elif lf == "luxury": lifestyles.append("phân khúc cao cấp")
                elif lf == "family": lifestyles.append("phù hợp gia đình")
                elif lf == "investment": lifestyles.append("phù hợp đầu tư")
                else: lifestyles.append(lf)
            if lifestyles:
                reasons.append(", ".join(lifestyles))
        if item.get("is_noi_bat") and len(reasons) < 2:
            reasons.append("tin nổi bật")
        return ", ".join(reasons) or "phù hợp tiêu chí tổng quan"


class ActionRouter:
    def __init__(self, chatbot: "BDSChatbot"):
        self.chatbot = chatbot

    def route(
        self,
        message: str,
        payload: Dict[str, Any],
        analysis: Dict[str, Any],
        mem: ChatMemory,
    ) -> Optional[Dict[str, Any]]:
        action = (
            (payload.get("action") or {}).get("type")
            if isinstance(payload.get("action"), dict)
            else payload.get("action_type")
        )
        action_payload = (
            (payload.get("action") or {}).get("payload")
            if isinstance(payload.get("action"), dict)
            else payload.get("action_payload")
        )
        print("ACTION =", action)
        print("MESSAGE =", message)
        normalized = normalize_text(message)
        if not action:
            action = self.detect_action(normalized, analysis, mem)
            action_payload = action_payload or {}
        if not action:
            return None
        if action.startswith("goi_tin/"):
            return self.handle_package_action(
                action, analysis, action_payload or payload, mem
            )
        if action.startswith("search/") or analysis.get("intent") == "refine_search":
            return None
        if action == "conversation/end":
            self.chatbot.memory.reset(
                payload.get("session_id") or "guest_default", "action_end"
            )
            return self.wrap_action(
                "Rất vui được hỗ trợ bạn. Khi cần tìm nhà, định giá hoặc xem gói tin, cứ nhắn mình nhé.",
                analysis,
                [],
                "conversation/end",
            )
        return None

    # FIX 3: Add ASCII support for detect_action
    def detect_action(
        self, text: str, analysis: Dict[str, Any], mem: ChatMemory
    ) -> Optional[str]:
        if analysis.get("intent") in ["package_buy_guide", "posting_guide"]:
            return None
            
        text_ascii = normalize_ascii(text)

        package_keywords = [
            "xem gói tin",
            "xem goi tin",
            "gói tin",
            "goi tin",
            "báo giá gói",
            "bao gia goi",
            "package",
        ]
        if any(k in text or k in text_ascii for k in package_keywords):
            return "goi_tin/list_packages"

        # --- explicit quick-reply action map ---
        QUICK_ACTION_MAP = [
            (["noi ngan sach", "nori ngan sach", "noi gia", "tang ngan sach", "no₳i ngan sach"],          "search/relax_budget"),
            (["tim khu vuc gan do", "khu vuc gan do", "quan lan can", "khu lan can", "gan do"],             "search/nearby_location"),
            (["doi loai bds tuong tu", "loai tuong tu", "bds tuong tu", "doi loai"],                       "search/similar_type"),
            (["tim can tuong tu", "can tuong tu", "tuong tu can nay", "tuong tu can do"],                  "search/similar_property"),
            (["dat lich xem nha", "dat lich xem", "appointment/start"],                                   "appointment/start"),
            (["dat lich can do", "dat lich can nay", "appointment/start_selected"],                        "appointment/start_selected"),
            (["xem chi tiet can do", "xem chi tiet can nay", "chi tiet can do"],                          "property/detail_selected"),
            (["cach mua goi tin", "huong dan mua goi"],                                                    "goi_tin/buy_guide"),
            (["huong dan dang bai", "huong dan dang tin", "cach dang tin"],                               "posting/guide"),
            (["xem lai goi tin", "xem goi tin", "bang gia goi tin"],                                      "goi_tin/list_packages"),
            (["xem can ho phu hop nhat", "xem can phu hop nhat"],                                         "search/escape_best"),
            (["xem tat ca bds gan bien"],                                                                 "search/escape_all_beach"),
            (["doi sang nha pho gan bien", "nha pho gan bien"],                                           "search/escape_townhouse_beach"),
        ]
        for keywords, action in QUICK_ACTION_MAP:
            if any(k in text_ascii for k in keywords):
                return action

        if any(
            k in text or k in text_ascii
            for k in ["đăng 10 tin", "dang 10 tin", "bao nhiêu tin", "bao nhieu tin"]
        ):
            return "goi_tin/recommend_package"
        if analysis.get("intent") == "package_info":
            return "goi_tin/list_packages"
        if mem.active_domain == "package" and any(
            k in text for k in ["đăng tin", "gói", "tin"]
        ):
            return "goi_tin/recommend_package"
        return None

    def handle_package_action(
        self, action: str, analysis: Dict[str, Any], payload: Dict[str, Any], mem: ChatMemory = None
    ) -> Dict[str, Any]:
        packages = self.chatbot.data.fetch_packages()
        print(f"📦 HANDLE_PACKAGE: Got {len(packages)} packages")
        if action == "goi_tin/recommend_package":
            response, quick = self.recommend_package(packages, payload)
        else:
            response, quick = self.chatbot.responses.packages(packages)
            
        if mem and mem.actor in {"customer", "guest"}:
            response += "<br><br><i>Để mua gói và đăng bài, bạn cần tài khoản môi giới trên hệ thống.</i>"
            quick = ["Cách trở thành môi giới", "Tìm BĐS", "Định giá BĐS"]
            
        return self.wrap_action(
            response, analysis, quick, action, suggestions_data=[]
        )

    def recommend_package(
        self, packages: List[Dict[str, Any]], payload: Dict[str, Any]
    ) -> Tuple[str, List[str]]:
        requested = self.extract_request_count(payload)
        if not packages:
            return self.chatbot.responses.packages(packages)
        ranked = sorted(packages, key=lambda x: self.package_score(x, requested))
        top = ranked[:3]
        lines = [f"Mình gợi ý gói phù hợp cho nhu cầu khoảng {requested or 'vài'} tin:"]
        for g in top:
            lines.append(
                f"• <b>{g.get('ten_goi')}</b>: {money_vnd(g.get('gia'))}/{g.get('so_ngay')} ngày, {g.get('so_luong_tin')} tin"
            )
        lines.append("Nếu bạn muốn, mình có thể gợi ý thêm.")
        return "<br>".join(lines), ["Cách mua gói tin", "Hướng dẫn đăng bài"]

    def package_score(self, package: Dict[str, Any], requested: Optional[int]) -> float:
        count = safe_int(package.get("so_luong_tin"), 0) or 0
        return abs(count - 10) if not requested else abs(count - requested)

    def extract_request_count(self, payload: Dict[str, Any]) -> Optional[int]:
        action_payload = payload.get("action") or payload.get("action_payload") or {}
        if (
            isinstance(action_payload, dict)
            and action_payload.get("requested_listing_count") is not None
        ):
            return safe_int(action_payload.get("requested_listing_count"))
        msg = normalize_text(str(payload.get("message") or ""))
        return self.chatbot.nlu.extract_listing_count(msg)

    def handle_search_action(
        self, action: str, analysis: Dict[str, Any], action_payload: Dict[str, Any], mem: Optional[ChatMemory] = None
    ) -> Dict[str, Any]:
        entities = deepcopy(analysis.get("entities") or {})
        if action_payload:
            entities.update(action_payload)
        ranked, relax_info = self.chatbot.find_ranked_properties(
            entities, analysis.get("intent"), mem=mem
        )
        draft, quick = self.chatbot.responses.search_response(
            ranked, entities, [], relax_info
        )
        return self.wrap_action(
            draft, analysis, quick, action, suggestions_data=ranked[:6]
        )

    def wrap_action(
        self,
        response: str,
        analysis: Dict[str, Any],
        quick: List[str],
        action_type: str,
        suggestions_data: Optional[List[Dict[str, Any]]] = None,
    ) -> Dict[str, Any]:
        suggestions = suggestions_data or []
        quick_reply_actions = [
            {
                "label": q,
                "action_type": self.detect_action(
                    normalize_text(q), analysis, ChatMemory()
                )
                or "search/refine",
                "payload": {"source": "quick_reply", "label": q},
            }
            for q in quick
        ]
        return {
            "status": True,
            "success": True,
            "response": response,
            "intent": analysis.get("intent"),
            "context": analysis.get("context"),
            "suggestions": suggestions,
            "quick_replies": quick,
            "data": {
                "reply": response,
                "intent": analysis.get("intent"),
                "context": analysis.get("context"),
                "action_type": action_type,
                "action_payload": {},
                "suggestions": suggestions,
                "quick_replies": quick_reply_actions,
                "is_markdown": True,
            },
        }


class ResponseGenerator:
    OPENERS = [
        "Mình gợi ý nhanh cho bạn",
        "Có vài lựa chọn khá hợp",
        "Mình lọc được một số tin đáng xem",
    ]

    def search_response(
        self,
        items: List[Dict[str, Any]],
        entities: Dict[str, Any],
        missing: List[str],
        relax_info: Optional[Dict[str, Any]] = None,
    ) -> Tuple[str, List[str]]:
        relax_info = relax_info or {"level": "exact", "message": ""}
        if missing and not items:
            labels = {
                "location": "khu vực bạn muốn tìm",
                "price": "ngân sách dự kiến",
                "property_type": "loại BĐS",
                "purpose": "mục đích mua/thuê",
            }
            qs = ", ".join(labels[m] for m in missing[:2])
            return f"Để tư vấn sát hơn, bạn cho mình biết thêm {qs} nhé.", [
                "Tìm căn hộ Đà Nẵng khoảng 2 tỷ",
                "Tôi mua để ở",
            ]
        if not items:
            msg = relax_info.get("message") if relax_info and relax_info.get("message") else self.no_result_message(entities)
            qr = ["Nới ngân sách 20%", "Mở rộng sang Sơn Trà/Ngũ Hành Sơn", "Xem căn hộ phù hợp nhất"] if relax_info and relax_info.get("message") == "Mình chưa thấy căn hộ đúng tiêu chí gần biển. Bạn muốn thử nới ngân sách, mở rộng khu vực biển hoặc xem các căn phù hợp nhất hiện có không?" else ["Nới ngân sách 20%", "Tìm khu vực gần đó", "Đổi loại BĐS tương tự"]
            return msg, qr
        
        limit = 3 if entities.get("refinement") else 5
        prefix = relax_info.get("message") or ""
        lines = [prefix] if prefix else []
        
        if entities.get("refinement"):
            rtype = entities.get("refinement_type")
            if rtype == "cheaper" or entities.get("superlative") == "cheapest":
                lines.append(f"Mình sắp xếp các căn phù hợp theo giá thấp đến cao cho bạn:")
            elif rtype == "larger" or entities.get("superlative") == "largest":
                lines.append(f"Mình ưu tiên các căn có diện tích lớn hơn trong cùng tiêu chí:")
            else:
                lines.append(f"Mình lọc {min(len(items), limit)} căn phù hợp nhất:")
        else:
            lines.append(f"{random.choice(self.OPENERS)} ({len(items)} kết quả phù hợp nhất):")
            
        for i, p in enumerate(items[:limit], 1):
            lines.append(
                f"{i}. <a href=\"{p['url']}\" target=\"_blank\"><b>{p['tieu_de']}</b></a> — {p['gia_text']}, {p.get('dien_tich')}m², {p.get('dia_chi') or 'đang cập nhật'}."
            )
            lines.append(f"   Lý do: {p.get('reason')}.")
        lines.append(
            "Bạn muốn mình lọc tiếp theo hướng rẻ hơn, rộng hơn, gần biển/trung tâm không?"
        )
        return "<br>".join(lines), [
            "Rẻ hơn",
            "Rộng hơn",
            "Gần biển",
            "Đặt lịch xem nhà",
        ]

    def no_result_message(self, entities: Dict[str, Any]) -> str:
        property_type = entities.get("property_type") or "BĐS"
        location = entities.get("location") or "khu vực này"
        budget = (
            money_vnd(entities.get("price_max"))
            if entities.get("price_max")
            else "ngân sách hiện tại"
        )
        return f"Hiện mình chưa thấy {property_type} đúng mức {budget} ở {location}. Mình có thể nới ngân sách khoảng 20% hoặc tìm quận lân cận."

    def relax_message(
        self, original: Dict[str, Any], relax_info: Dict[str, Any]
    ) -> str:
        property_type = original.get("property_type") or "BĐS"
        location = original.get("location") or "khu vực bạn chọn"
        budget = (
            money_vnd(original.get("price_max"))
            if original.get("price_max")
            else "ngân sách ban đầu"
        )
        level = relax_info.get("level")
        if level == "budget_20":
            return f"Chưa có {property_type} khớp sát {budget} ở {location}. Mình nới ngân sách khoảng 20% để bạn tham khảo:"
        if level == "nearby_location":
            return f"Chưa có lựa chọn sát {budget} ở {location}. Mình mở sang khu lân cận để tránh gợi ý quá xa:"
        return ""

    def packages(self, packages: List[Dict[str, Any]]) -> Tuple[str, List[str]]:
        print(f"📦 RESPONSE_GENERATOR.packages() called with {len(packages)} packages")
        if not packages:
            print("⚠️ packages list is EMPTY - returning fallback message")
            return (
                'Hiện mình chưa đọc được bảng gói tin. Bạn có thể vào <a href="/moi-gioi/goi-tin" target="_blank">trang gói tin</a> hoặc nhắn mình nhu cầu đăng bao nhiêu tin để tư vấn tiếp.',
                ["Tôi muốn đăng 10 tin", "Xem gói tin"],
            )
        lines = [
            "Với môi giới, bạn có thể chọn gói theo số lượng tin và nhu cầu hiển thị:"
        ]
        for g in packages[:5]:
            lines.append(
                f"• <b>{g.get('ten_goi')}</b>: {money_vnd(g.get('gia'))}/{g.get('so_ngay')} ngày, {g.get('so_luong_tin')} tin"
                + (" — có nhãn VIP" if g.get("gan_nhan_vip") else "")
            )
        lines.append(
            "Nếu bạn cho mình biết mỗi tháng dự kiến đăng bao nhiêu tin, mình sẽ gợi ý gói tiết kiệm nhất."
        )
        return "<br>".join(lines), [
            "Cách mua gói tin",
            "Hướng dẫn đăng bài",
        ]

    def property_detail_response(self, item: Dict[str, Any]) -> Tuple[str, List[str]]:
        title = item.get("tieu_de") or "BĐS này"
        url = item.get("url") or "#"
        price = item.get("gia_text") or money_vnd(item.get("gia"))
        area = (
            f"{item.get('dien_tich')}m²"
            if item.get("dien_tich")
            else "đang cập nhật diện tích"
        )
        address = item.get("dia_chi") or "đang cập nhật địa chỉ"
        region = self.property_region(address)
        highlights = self.property_highlights(item, region)
        reason = item.get("reason") or "khớp ngân sách và loại BĐS bạn đang tìm"
        bedrooms = (
            f", {item.get('so_phong_ngu')} phòng ngủ"
            if item.get("so_phong_ngu")
            else ""
        )
        response = f'<a href="{url}" target="_blank"><b>{title}</b></a><br>Giá {price}, diện tích {area}{bedrooms}. Khu vực: {region}.<br>Lý do nên xem: {reason}.<br>Điểm nổi bật: {", ".join(highlights)}.<br>Nếu lịch của bạn tiện, mình có thể giữ căn này và lấy thông tin đặt lịch ngay.'
        quick = ["Đặt lịch căn đó", "Tìm căn tương tự", "Rẻ hơn"]
        return response, quick

    def property_appointment_response(
        self, item: Dict[str, Any]
    ) -> Tuple[str, List[str]]:
        title = item.get("tieu_de") or "BĐS này"
        return (
            f"Mình đặt lịch xem <b>{title}</b> cho bạn nhé. Bạn muốn xem ngày nào?",
            ["Tối mai", "Cuối tuần", "Thứ 7"],
        )

    def booking_question(
        self, booking_state: Dict[str, Any], missing_field: Optional[str]
    ) -> Tuple[str, List[str]]:
        title = booking_state.get("property_title") or "căn này"
        if missing_field == "date":
            return (
                f"Mình đặt lịch xem <b>{title}</b> cho bạn nhé. Bạn muốn xem ngày nào?",
                ["Tối mai", "Cuối tuần", "Thứ 7"],
            )
        if missing_field == "time":
            date_label = booking_state.get("date_label") or booking_state.get("date")
            return f"Được, mình ghi nhận {date_label}. Khoảng mấy giờ phù hợp?", [
                "7h",
                "19h30",
                "9h sáng",
            ]
        return "Mình đã đủ thông tin để đặt lịch.", []

    def booking_ready_response(
        self, booking_state: Dict[str, Any]
    ) -> Tuple[str, List[str]]:
        title = booking_state.get("property_title") or "căn này"
        date_label = booking_state.get("date_label") or booking_state.get("date")
        response = f"Mình đã có đủ thông tin lịch xem <b>{title}</b> vào {date_label} lúc {booking_state.get('time')}. Đang gửi yêu cầu đặt lịch..."
        return response, ["Xem chi tiết căn đó", "Tìm căn tương tự", "Rẻ hơn"]

    def login_required_for_booking(self, item: Dict[str, Any]) -> Tuple[str, List[str]]:
        title = item.get("tieu_de") or "căn này"
        return (
            f"Để đặt lịch xem <b>{title}</b>, bạn cần đăng nhập tài khoản khách hàng trước.",
            ["Xem chi tiết căn đó", "Tìm căn tương tự"],
        )

    def property_region(self, address: str) -> str:
        parts = [part.strip() for part in (address or "").split(",") if part.strip()]
        return (
            ", ".join(parts[-2:])
            if len(parts) >= 2
            else address or "đang cập nhật khu vực"
        )

    def property_highlights(self, item: Dict[str, Any], region: str) -> List[str]:
        highlights = []
        if item.get("dien_tich"):
            highlights.append(f"{item.get('dien_tich')}m² dễ bố trí")
        if item.get("so_phong_ngu"):
            highlights.append(f"{item.get('so_phong_ngu')} phòng ngủ")
        if item.get("is_noi_bat"):
            highlights.append("tin nổi bật")
        if region and region != "đang cập nhật khu vực":
            highlights.append(f"vị trí {region}")
        if item.get("gia_text") or item.get("gia"):
            highlights.append("giá sát ngân sách")
        return highlights[:4] or [
            "thông tin cơ bản rõ ràng",
            "phù hợp để đi xem thực tế",
        ]

    def simple(self, intent: str) -> Tuple[str, List[str]]:
        mapping = {
            "valuation": (
                'Bạn có thể dùng công cụ <a href="/khach-hang/dinh-gia-ai" target="_blank">Định giá AI</a>.',
                ["Định giá căn hộ", "Tìm BĐS cùng khu vực"],
            ),
            "appointment": (
                "Để đặt lịch xem nhà, bạn mở trang chi tiết BĐS rồi chọn liên hệ/đặt lịch với môi giới.",
                ["Tìm căn phù hợp", "Liên hệ môi giới"],
            ),
            "posting_guide": (
                "Hướng dẫn đăng bài BĐS:<br>1. Đăng nhập tài khoản môi giới.<br>2. Vào mục <b>Đăng tin BĐS</b> trên dashboard.<br>3. Điền tiêu đề, chọn loại BĐS (căn hộ, nhà phố, đất nền...).<br>4. Nhập giá bán/cho thuê, diện tích, số phòng ngủ, số phòng tắm.<br>5. Nhập địa chỉ đầy đủ (tỉnh/thành, quận/huyện, phường/xã, số nhà).<br>6. Thêm mô tả chi tiết và tải ảnh bất động sản (tối đa 10 ảnh).<br>7. Chọn trạng thái <b>Đăng ngay</b> (cần có gói tin active) hoặc <b>Lưu nháp</b>.<br>8. Bấm Gửi — hệ thống sẽ gửi admin duyệt. Sau khi duyệt, tin hiển thị công khai.",
                ["Cách mua gói tin", "Xem lại gói tin"],
            ),
            "package_buy_guide": (
                "Cách mua gói tin:<br>1. Đăng nhập tài khoản môi giới.<br>2. Vào mục <b>Gói tin</b> trên dashboard.<br>3. Xem danh sách gói: số lượng tin đăng, số ngày hiệu lực và giá.<br>4. Chọn gói phù hợp, bấm <b>Mua gói</b>.<br>5. Hệ thống tạo đơn thanh toán — quét mã QR qua SePay (chuyển khoản ngân hàng).<br>6. Sau khi chuyển khoản thành công, hệ thống tự động xác nhận và cộng ngay:<br>&nbsp;&nbsp;&nbsp;• Số lượt đăng tin (so_tin_con_lai) vào tài khoản.<br>&nbsp;&nbsp;&nbsp;• Thời hạn gói (ngay_het_han_goi) được gia hạn theo số ngày của gói.<br>7. Bạn có thể đăng tin ngay sau khi gói được kích hoạt.",
                ["Hướng dẫn đăng bài", "Xem lại gói tin"],
            ),
            "become_broker_guide": (
                "<b>Cách trở thành môi giới:</b><br>1. Đăng xuất tài khoản khách hàng (nếu đang đăng nhập).<br>2. Truy cập vào mục Đăng ký ở góc phải trên, chọn Đăng ký môi giới (hoặc vào link /moi-gioi/dang-ky).<br>3. Điền đầy đủ thông tin cá nhân.<br>4. Đăng nhập tài khoản môi giới vừa tạo.<br>Sau khi có tài khoản môi giới, bạn có thể mua gói tin và đăng bài BĐS trên hệ thống.",
                ["Xem bảng giá gói tin", "Tìm BĐS", "Định giá BĐS"],
            ),
            "out_of_domain": (
                "Mình là trợ lý bất động sản nên chỉ hỗ trợ tìm nhà đất, định giá, gói tin, đặt lịch.",
                ["Tìm BĐS Đà Nẵng", "Định giá nhà", "Gói tin đăng bài"],
            ),
            "ending": (
                "Rất vui được hỗ trợ bạn. Khi cần tìm nhà, định giá hoặc đặt lịch xem BĐS, cứ nhắn mình nhé.",
                [],
            ),
            "empty": (
                "Bạn nhập giúp mình nhu cầu cụ thể hơn nhé, ví dụ: 'tìm căn hộ Sơn Trà khoảng 2 tỷ'.",
                ["Tìm căn hộ Đà Nẵng", "Xem gói tin"],
            ),
        }
        return mapping.get(
            intent,
            (
                "Mình có thể hỗ trợ tìm BĐS, gợi ý căn phù hợp, tư vấn gói tin, định giá và đặt lịch xem nhà.",
                ["Tìm BĐS", "Định giá", "Gói tin"],
            ),
        )


class BDSChatbot:
    def __init__(self):
        self.memory = MemoryManager()
        self.gemini = GeminiPipeline()
        self.nlu = RuleBasedNLU()
        self.data = BDSDataFetcher()
        self.recommender = PropertyRecommendationEngine()
        self.responses = ResponseGenerator()
        self.router = ActionRouter(self)

    def handle_start_booking(self, message: str, mem: ChatMemory, payload: Dict[str, Any], start: float) -> Optional[Dict[str, Any]]:
        msg_ascii = normalize_ascii(message)
        analysis = {"intent": "appointment", "booking_flow": True, "context": "booking", "entities": {}}
        session_id = payload.get("session_id", "guest_default")[:100]
        
        # User requested specific check before resolve_property_reference
        clean_msg = normalize_ascii(message)
        is_simple_booking = any(k in clean_msg for k in [
            "dat lich",
            "hen xem",
            "xem nha",
            "lich xem",
        ])

        # Detect if user explicitly pointed to a specific property
        # (e.g. "Đặt lịch căn đó", "đặt lịch căn này", "đặt lịch căn 1")
        is_explicit_property_ref = any(k in clean_msg for k in [
            "can do", "can nay", "can 1", "can 2", "can 3",
            "can mot", "can hai", "can ba",
            "nha nay", "nha do",
        ])

        # If user tapped generic "Đặt lịch xem nhà" from a multi-result list
        # (≥2 results and NOT an explicit property reference), ask which property.
        # Exception: if user already explicitly viewed a specific property
        # (last_intent == property_detail), use selected_property directly.
        user_already_picked = mem.last_intent in ["property_detail", "appointment"]
        if (
            is_simple_booking
            and not is_explicit_property_ref
            and not user_already_picked
            and len(mem.last_results or []) >= 2
        ):
            mem.active_context = "booking"
            mem.booking_state = {
                "status": "collecting",
                "step": "need_property",
                "property_id": None,
                "property_title": None,
                "date": None,
                "time": None,
            }
            n = min(len(mem.last_results), 3)
            draft = "Bạn muốn xem căn nào trong danh sách trên? Ví dụ: căn 1 hoặc căn 2."
            quick = [f"Căn {i+1}" for i in range(n)] + ["Hủy"]
            analysis["booking_state"] = deepcopy(mem.booking_state)
            self.memory.update(session_id, message, draft, analysis, [])
            return self.wrap(draft, analysis, [], quick, start)

        if is_simple_booking and isinstance(mem.selected_property, dict):
            property = mem.selected_property
            mem.active_context = "booking"
            mem.booking_state = {
                "status": "collecting",
                "step": "need_date",
                "property_id": property.get("id"),
                "property_title": property.get("tieu_de"),
                "broker_id": property.get("moi_gioi_id"),
                "date": None,
                "date_label": None,
                "time": None,
                "customer_id": None
            }
            draft = f"Mình đặt lịch xem {property.get('tieu_de')} cho bạn nhé. Bạn muốn xem ngày nào?"
            quick = ["Tối mai", "Cuối tuần", "Thứ 7"]
            analysis["booking_state"] = deepcopy(mem.booking_state)
            self.memory.update(session_id, message, draft, analysis, [])
            return self.wrap(draft, analysis, [], quick, start)

        # CASE 1 & 2: Có thể xác định căn nhà qua câu lệnh
        property = resolve_property_reference(message, mem)
        if property:
            mem.selected_property = property
            mem.active_context = "booking"
            mem.booking_state = {
                "status": "collecting",
                "step": "need_date",
                "property_id": property.get("id"),
                "property_title": property.get("tieu_de"),
                "broker_id": property.get("moi_gioi_id"),
                "date": None,
                "date_label": None,
                "time": None,
                "customer_id": None
            }
            draft = f"Mình đặt lịch xem {property.get('tieu_de')} cho bạn nhé. Bạn muốn xem ngày nào?"
            quick = ["Tối mai", "Cuối tuần", "Thứ 7"]
            analysis["booking_state"] = deepcopy(mem.booking_state)
            self.memory.update(session_id, message, draft, analysis, [])
            return self.wrap(draft, analysis, [], quick, start)
            
        # CASE 3: Chưa chỉ căn nhưng có last_results
        if mem.last_results:
            mem.active_context = "booking"
            mem.booking_state = {
                "status": "collecting",
                "step": "need_property",
                "property_id": None,
                "property_title": None,
                "date": None,
                "time": None
            }
            draft = "Bạn muốn chọn căn số mấy trong danh sách trên để đặt lịch?"
            quick = [f"Căn {i+1}" for i in range(min(3, len(mem.last_results)))] + ["Hủy"]
            analysis["booking_state"] = deepcopy(mem.booking_state)
            self.memory.update(session_id, message, draft, analysis, [])
            return self.wrap(draft, analysis, [], quick, start)
            
        # CASE 4: Không có last_results
        mem.active_context = "booking"
        mem.booking_state = {
            "status": "collecting",
            "step": "need_property",
            "property_id": None,
            "property_title": None,
            "date": None,
            "time": None
        }
        draft = "Bạn muốn đặt lịch xem loại BĐS nào? Cho mình biết loại BĐS, khu vực, khoảng giá và diện tích mong muốn nhé."
        quick = ["Căn hộ khoảng 4 tỷ", "Nhà phố Sơn Trà", "Gần biển"]
        analysis["booking_state"] = deepcopy(mem.booking_state)
        self.memory.update(session_id, message, draft, analysis, [])
        return self.wrap(draft, analysis, [], quick, start)

    def handle_booking_flow(self, message: str, mem: ChatMemory, payload: Dict[str, Any], start: float) -> Optional[Dict[str, Any]]:
        bs = mem.booking_state or {}
        analysis = {"intent": "appointment", "booking_flow": True, "context": "booking", "entities": {}}
        session_id = payload.get("session_id", "guest_default")[:100]
        
        # 1. Chưa có property_id
        if not bs.get("property_id"):
            property = resolve_property_reference(message, mem)
            if property:
                bs["property_id"] = property.get("id")
                bs["property_title"] = property.get("tieu_de")
                bs["broker_id"] = property.get("moi_gioi_id")
                bs["status"] = "collecting"
                bs["step"] = "need_date"
                mem.selected_property = property
                mem.active_context = "booking"
                mem.booking_state = bs
                draft = f"Mình đặt lịch xem {property.get('tieu_de')} cho bạn nhé. Bạn muốn xem ngày nào?"
                quick = ["Tối mai", "Cuối tuần", "Thứ 7"]
                analysis["booking_state"] = deepcopy(bs)
                self.memory.update(session_id, message, draft, analysis, [])
                return self.wrap(draft, analysis, [], quick, start)
                
            rule = self.nlu.analyze(message, mem)
            if rule.get("intent") in ["search_property", "refine_search"]:
                bs["step"] = "need_property"
                return None
                
            if mem.last_results:
                draft = "Bạn muốn chọn căn số mấy trong danh sách trên để đặt lịch?"
                quick = [f"Căn {i+1}" for i in range(min(3, len(mem.last_results)))] + ["Hủy"]
                analysis["booking_state"] = deepcopy(bs)
                self.memory.update(session_id, message, draft, analysis, [])
                return self.wrap(draft, analysis, [], quick, start)
                
            draft = "Bạn muốn đặt lịch xem loại BĐS nào? Cho mình biết loại BĐS, khu vực, khoảng giá và diện tích mong muốn nhé."
            quick = ["Căn hộ khoảng 4 tỷ", "Nhà phố Sơn Trà", "Gần biển"]
            analysis["booking_state"] = deepcopy(bs)
            self.memory.update(session_id, message, draft, analysis, [])
            return self.wrap(draft, analysis, [], quick, start)

        # 2. Đã có property_id nhưng chưa có date
        if bs.get("property_id") and not bs.get("date"):
            date_info = parse_booking_date(message)
            if date_info and date_info.get("date"):
                bs["date"] = date_info["date"]
                bs["date_label"] = date_info.get("date_label")
                if date_info.get("preferred_period"):
                    bs["preferred_period"] = date_info["preferred_period"]
                bs["step"] = "need_time"
                mem.booking_state = bs
                mem.active_context = "booking"
                draft = f"Được, mình ghi nhận {bs.get('date_label')}. Khoảng mấy giờ phù hợp?"
                quick = ["7h", "19h", "19h30", "9h sáng"]
                analysis["booking_state"] = deepcopy(bs)
                self.memory.update(session_id, message, draft, analysis, [])
                return self.wrap(draft, analysis, [], quick, start)
            else:
                draft = "Bạn muốn xem ngày nào? Ví dụ: tối mai, cuối tuần hoặc 15/05."
                quick = ["Tối mai", "Cuối tuần", "Thứ 7"]
                analysis["booking_state"] = deepcopy(bs)
                self.memory.update(session_id, message, draft, analysis, [])
                return self.wrap(draft, analysis, [], quick, start)

        # 3. Đã có property_id + date nhưng chưa có time
        if bs.get("property_id") and bs.get("date") and not bs.get("time"):
            time_value = parse_booking_time(message, bs)
            if time_value:
                bs["time"] = time_value
                bs["step"] = "ready_to_create"
                bs["status"] = "ready"
                mem.booking_state = bs
                
                analysis["booking_request"] = {
                    "bat_dong_san_id": bs.get("property_id"),
                    "ngay_hen": bs.get("date"),
                    "gio_hen": bs.get("time"),
                    "ghi_chu": "Đặt lịch qua chatbot",
                }
                analysis["booking_state"] = deepcopy(bs)
                draft, quick = self.responses.booking_ready_response(bs)
                self.memory.update(session_id, message, draft, analysis, [])
                return self.wrap(draft, analysis, [], quick, start)
            else:
                draft = "Bạn muốn xem lúc mấy giờ? Ví dụ: 19h hoặc 9h sáng."
                quick = ["7h", "19h", "19h30", "9h sáng"]
                analysis["booking_state"] = deepcopy(bs)
                self.memory.update(session_id, message, draft, analysis, [])
                return self.wrap(draft, analysis, [], quick, start)
                
        if bs.get("status") == "ready" or bs.get("step") == "created":
            if mem.selected_property:
                mem.last_booked_property = deepcopy(mem.selected_property)
            mem.booking_state = {}
            mem.active_context = "search"
            return None
            
        return None

    def process(self, payload: Dict[str, Any]) -> Dict[str, Any]:
        start = time.time()
        message = str(payload.get("message", ""))
        message = re.sub(r"[\'\"`’\.\!\?]+$", "", message.strip()).strip()
        session_id = (payload.get("session_id") or "guest_default")[:100]
        actor = normalize_actor(payload)
        role = payload.get("role") or "guest"
        mem = self.memory.get(session_id)
        mem.actor = actor
        msg_ascii = normalize_ascii(message)

        # Broker: "dat lich / lich hen" -> redirect to appointments page, not booking
        if actor == "broker" and any(k in normalize_ascii(message) for k in [
            "dat lich xem nha", "lich xem nha", "xem lich hen",
            "lich hen", "lich khach", "lich cua toi", "lich khen",
        ]):
            draft_b = (
                "B\u1ea1n mu\u1ed1n xem l\u1ecbch h\u1eb9n c\u1ee7a kh\u00e1ch? V\u00e0o m\u1ee5c "
                "<b>L\u1ecbch h\u1eb9n xem nh\u00e0</b> tr\u00ean dashboard \u0111\u1ec3 xem "
                "danh s\u00e1ch l\u1ecbch ch\u1edd x\u00e1c nh\u1eadn v\u00e0 \u0111\u00e3 x\u00e1c nh\u1eadn t\u1eeb kh\u00e1ch h\u00e0ng."
            )
            a_b = {"intent": "broker_appointments", "context": "broker", "entities": {}}
            self.memory.update(session_id, message, draft_b, a_b, [])
            return self.wrap(draft_b, a_b, [], [
                "L\u1ecbch ch\u1edd x\u00e1c nh\u1eadn", "G\u00f3i tin \u0111\u0103ng B\u0110S", "H\u01b0\u1edbng d\u1eabn \u0111\u0103ng b\u00e0i"
            ], start)

        # 3. Check cancel booking
        if is_booking_cancel_intent(message) and mem.active_context == "booking":
            mem.booking_state = {}
            mem.active_context = "search"
            analysis = {"intent": "cancel_booking", "context": "search", "entities": {}, "booking_state": {}}
            draft = "Mình đã hủy thao tác đặt lịch. Bạn cần hỗ trợ gì khác không?"
            self.memory.update(session_id, message, draft, analysis, [])
            return self.wrap(draft, analysis, [], ["Tìm BĐS", "Định giá"], start)
            
        # 4. Handle Active Booking Flow BEFORE anything else — ONLY for customers
        is_active_booking = (
            actor in {"customer", "guest"}
            and (
                mem.active_context == "booking"
                or (mem.booking_state and mem.booking_state.get("status") == "collecting")
                or (mem.booking_state and mem.booking_state.get("step") in ["need_property", "need_date", "need_time"])
            )
        )
        if is_active_booking:
            resp = self.handle_booking_flow(message, mem, payload, start)
            if resp:
                return resp
                
        # 5. Handle Start Booking Keywords — customer only
        is_escape_best_local = (payload.get("action") or {}).get("type") == "search/escape_best" or payload.get("action_type") == "search/escape_best" or any(k in normalize_ascii(message) for k in ["xem can ho phu hop", "xem can phu hop"])
        has_booking_kw = any(k in msg_ascii for k in ["dat lich", "hen xem", "xem nha", "tham quan", "lich xem", "xem can"]) and not is_escape_best_local
        if has_booking_kw and actor in {"customer", "guest"}:
            resp = self.handle_start_booking(message, mem, payload, start)
            if resp:
                return resp
        elif has_booking_kw and actor == "broker":
            # Broker typing booking keywords = wants to see their schedule
            draft_br = (
                "Bạn muốn xem lịch hẹn của khách? Vào mục "
                "<b>Lịch hẹn xem nhà</b> trên dashboard để quản lý."
            )
            a_br = {"intent": "broker_appointments", "context": "broker", "entities": {}}
            self.memory.update(session_id, message, draft_br, a_br, [])
            return self.wrap(draft_br, a_br, [], [
                "Lịch chờ xác nhận", "Gói tin đăng BĐS", "Hướng dẫn đăng bài"
            ], start)
        
        # 5b. Early return for guide intents (before LLM/router, to prevent falling into search)
        rule_quick = self.nlu.analyze(message, mem)
        _qi = rule_quick.get("intent")
        guide_intent = _qi if _qi in ["package_buy_guide", "posting_guide", "become_broker_guide"] else None

        # package_info early-return
        if _qi == "package_info" and rule_quick.get("context") == "package":
            packages = self.data.fetch_packages()
            draft_p, quick_p = self.responses.packages(packages)
            
            if actor in {"customer", "guest"}:
                draft_p += "<br><br><i>Để mua gói và đăng bài, bạn cần tài khoản môi giới trên hệ thống.</i>"
                quick_p = ["Cách trở thành môi giới", "Tìm BĐS", "Định giá BĐS"]
                
            pkg_analysis = {"intent": "package_info", "context": "package", "entities": {}}
            self.memory.update(session_id, message, draft_p, pkg_analysis, [])
            return self.wrap(draft_p, pkg_analysis, [], quick_p, start)

        if guide_intent:
            if actor == "customer":
                pol = RolePolicy._customer_redirect(guide_intent)
                pol["processing_time"] = round(time.time() - start, 3)
                return pol
            draft, quick = self.responses.simple(guide_intent)
            guide_analysis = {"intent": guide_intent, "context": rule_quick.get("context", "package"), "entities": {}}
            self.memory.update(session_id, message, draft, guide_analysis, [])
            return self.wrap(draft, guide_analysis, [], quick, start)

        # 5c. Quick-reply smart actions (relax_budget, nearby, similar)
        msg_ascii_lower = normalize_ascii(message)
        is_all_prop       = any(k in msg_ascii_lower for k in ["xem tat ca bat dong san", "xem tat ca bds", "tat ca bat dong san", "tat ca tin", "xem tat ca tin dang", "danh sach bat dong san"]) or msg_ascii_lower in ["tim bds", "tim bat dong san"]
        is_same_loc       = any(k in msg_ascii_lower for k in ["tim bds cung khu vuc", "tim bat dong san cung khu vuc", "tim can cung khu", "cung khu vuc"])
        action_type_local = (payload.get("action") or {}).get("type") if isinstance(payload.get("action"), dict) else payload.get("action_type")
        is_escape_best    = action_type_local == "search/escape_best" or any(k in msg_ascii_lower for k in ["xem can ho phu hop", "xem can phu hop"])
        is_escape_all_b   = action_type_local == "search/escape_all_beach" or "xem tat ca bds gan bien" in msg_ascii_lower
        is_escape_th_b    = action_type_local == "search/escape_townhouse_beach" or "doi sang nha pho gan bien" in msg_ascii_lower
        is_relax_budget   = any(k in msg_ascii_lower for k in ["noi ngan sach", "noi gia", "tang ngan sach", "tang gia"])
        is_nearby_loc     = any(k in msg_ascii_lower for k in ["tim khu vuc gan do", "khu vuc gan do", "quan lan can", "khu lan can"])
        is_similar_type   = any(k in msg_ascii_lower for k in ["doi loai bds tuong tu", "loai tuong tu", "bds tuong tu", "doi loai bds"])
        is_similar_prop   = any(k in msg_ascii_lower for k in ["tim can tuong tu", "can tuong tu", "tuong tu can nay", "tuong tu can do"])

        if is_escape_best:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            base = deepcopy(mem.last_search_filters or {})
            base.pop("lifestyle", None)
            base.pop("locations", None)
            base.pop("refinement_type", None)
            base.pop("refinement", None)
            base.pop("superlative", None)
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info)
            draft_esc = "Mình quay lại danh sách các căn hộ phù hợp ban đầu cho bạn:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], quick_esc, start)

        if is_escape_all_b:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            base = {"lifestyle": ["near_beach"], "locations": ["sơn trà", "ngũ hành sơn"]}
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info)
            draft_esc = "Đây là tất cả BĐS gần biển hiện có:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], ["Căn hộ", "Nhà phố", "Khoảng 4 tỷ", "Đặt lịch xem nhà"], start)

        if is_escape_th_b:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            base = deepcopy(mem.last_search_filters or {})
            base.pop("price_max", None)
            base.pop("price_min", None)
            base["property_type"] = "nhà phố"
            base["lifestyle"] = ["near_beach"]
            base["locations"] = ["sơn trà", "ngũ hành sơn"]
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info)
            draft_esc = "Mình chuyển sang tìm nhà phố gần biển cho bạn:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], quick_esc, start)

        if is_all_prop:
            base = {}
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            if ranked:
                draft_all, quick_all = self.responses.search_response(ranked, base, [], relax_info)
                draft_all = "Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>" + draft_all
            else:
                draft_all = "Hiện tại chưa có BĐS nào phù hợp."
                quick_all = ["Căn hộ", "Nhà phố", "Gần biển", "Khoảng 4 tỷ"]
            all_a = {"intent": "search_property", "context": "search", "entities": {}}
            self.memory.update(session_id, message, draft_all, all_a, ranked)
            return self.wrap(draft_all, all_a, ranked[:6], ["Căn hộ", "Nhà phố", "Gần biển", "Khoảng 4 tỷ"], start)

        if is_same_loc:
            base = {}
            if mem.selected_property and isinstance(mem.selected_property, dict) and mem.selected_property.get("dia_chi"):
                prop = mem.selected_property
                for loc_k in DA_NANG_NEARBY_DISTRICTS:
                    if loc_k in prop["dia_chi"].lower():
                        base["location"] = loc_k
                        break
                if prop.get("loai"): base["property_type"] = prop["loai"]
            elif mem.active_context == "valuation" and mem.entities.get("location"):
                base["location"] = mem.entities.get("location")
                
            if base.get("location"):
                ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
                if mem.selected_property and isinstance(mem.selected_property, dict) and mem.selected_property.get("id"):
                    ranked = [r for r in ranked if str(r.get("id")) != str(mem.selected_property.get("id"))]
                if ranked:
                    draft_loc, quick_loc = self.responses.search_response(ranked, base, [], relax_info)
                    draft_loc = f"Mình tìm thêm vài BĐS cùng khu vực {base['location'].title()} cho bạn:<br>" + draft_loc
                else:
                    draft_loc = f"Mình chưa tìm thấy thêm căn nào khác ở khu vực {base['location'].title()}."
                    quick_loc = ["Tìm BĐS", "Định giá BĐS"]
                loc_a = {"intent": "search_property", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft_loc, loc_a, ranked)
                return self.wrap(draft_loc, loc_a, ranked[:6], quick_loc, start)
            else:
                draft_loc = "Bạn muốn tìm BĐS cùng khu vực nào? Ví dụ Hải Châu, Sơn Trà hoặc Thanh Khê."
                self.memory.update(session_id, message, draft_loc, {"intent": "general_bds", "entities": {}}, [])
                return self.wrap(draft_loc, {"intent": "general_bds"}, [], ["Hải Châu", "Sơn Trà", "Thanh Khê"], start)

        if is_relax_budget:
            base = deepcopy(mem.last_no_result_filters or mem.last_search_filters or mem.entities or {})
            price_max = base.get("price_max")
            if price_max:
                new_max = int(float(price_max) * 1.2)
                base["price_max"] = new_max
                if base.get("price_min"):
                    base["price_min"] = int(float(base["price_min"]) * 1.2)
                ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
                new_budget_text = money_vnd(new_max)
                if ranked:
                    draft_r, quick_r = self.responses.search_response(ranked, base, [], relax_info)
                    draft_r = f"Mình đã nới ngân sách lên khoảng {new_budget_text} và tìm lại cho bạn:<br>" + draft_r
                else:
                    mem.no_result_context["tried_actions"].append("relax_budget")
                    if "nearby_location" in mem.no_result_context["tried_actions"]:
                        draft_r = "Mình đã thử nới ngân sách và mở rộng khu vực nhưng vẫn chưa có căn hộ gần biển phù hợp. Bạn có thể xem các căn hộ phù hợp nhất hiện có hoặc đổi sang loại BĐS gần biển khác."
                        quick_r = ["Xem căn hộ phù hợp nhất", "Đổi sang nhà phố gần biển", "Xem tất cả BĐS gần biển"]
                    else:
                        draft_r = f"Sau khi nới ngân sách lên khoảng {new_budget_text}, mình vẫn chưa thấy căn phù hợp. Bạn muốn mở rộng khu vực hoặc đổi sang nhà phố không?"
                        quick_r = ["Mở rộng sang Sơn Trà/Ngũ Hành Sơn", "Đổi sang nhà phố gần biển", "Xem căn hộ phù hợp nhất"]
                    ranked = []
                relax_a = {"intent": "refine_search", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft_r, relax_a, ranked)
                return self.wrap(draft_r, relax_a, ranked[:6], quick_r, start)
            else:
                draft_r = "Bạn cho mình biết ngân sách hiện tại của bạn là bao nhiêu để mình nới lên nhé?"
                self.memory.update(session_id, message, draft_r, {"intent": "general_bds", "entities": {}}, [])
                return self.wrap(draft_r, {"intent": "general_bds"}, [], ["Khoảng 2 tỷ", "Khoảng 4 tỷ"], start)

        if is_nearby_loc:
            base = deepcopy(mem.last_no_result_filters or mem.last_search_filters or mem.entities or {})
            loc = (base.get("location") or base.get("locations", [None])[0] or "").lower()
            loc_ascii = normalize_ascii(loc)
            nearby = DA_NANG_NEARBY_DISTRICTS.get(loc) or DA_NANG_NEARBY_DISTRICTS.get(loc_ascii)
            if nearby and loc:
                base["locations"] = [loc] + nearby
                base.pop("location", None)
                ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
                nearby_txt = ", ".join(nearby[:3])
                if ranked:
                    draft_nb, quick_nb = self.responses.search_response(ranked, base, [], relax_info)
                    draft_nb = f"Mình thử mở rộng sang khu vực lân cận ({nearby_txt}):<br>" + draft_nb
                else:
                    mem.no_result_context["tried_actions"].append("nearby_location")
                    if "relax_budget" in mem.no_result_context["tried_actions"]:
                        draft_nb = "Mình đã thử nới ngân sách và mở rộng khu vực nhưng vẫn chưa có căn hộ gần biển phù hợp. Bạn có thể xem các căn hộ phù hợp nhất hiện có hoặc đổi sang loại BĐS gần biển khác."
                        quick_nb = ["Xem căn hộ phù hợp nhất", "Đổi sang nhà phố gần biển", "Xem tất cả BĐS gần biển"]
                    else:
                        draft_nb = f"Mình đã mở sang {nearby_txt} nhưng vẫn chưa có kết quả. Bạn muốn nới ngân sách thêm không?"
                        quick_nb = ["Nới ngân sách thêm", "Đổi sang nhà phố gần biển", "Xem tất cả BĐS gần biển"]
                    ranked = []
                nb_a = {"intent": "refine_search", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft_nb, nb_a, ranked)
                return self.wrap(draft_nb, nb_a, ranked[:6], quick_nb, start)
            else:
                draft_nb = "Bạn muốn tìm khu vực gần đâu? Ví dụ: Hải Châu, Sơn Trà hoặc Ngũ Hành Sơn."
                self.memory.update(session_id, message, draft_nb, {"intent": "general_bds", "entities": {}}, [])
                return self.wrap(draft_nb, {"intent": "general_bds"}, [], ["Hải Châu", "Sơn Trà", "Ngũ Hành Sơn"], start)

        if is_similar_type:
            TYPE_SIMILAR = {
                "căn hộ": ["nhà phố", "studio"],
                "chung cư": ["nhà phố", "căn hộ"],
                "nhà phố": ["nhà riêng", "biệt thự"],
                "nhà riêng": ["nhà phố", "biệt thự"],
                "đất nền": ["đất thổ cư"],
                "biệt thự": ["nhà phố", "nhà riêng"],
            }
            base = deepcopy(mem.last_search_filters or mem.entities or {})
            cur_type = (mem.current_property_type or base.get("property_type") or "").lower()
            alt_types = None
            for k, v in TYPE_SIMILAR.items():
                if k in cur_type:
                    alt_types = v
                    break
            if alt_types:
                base["property_type"] = alt_types[0]
                ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
                if ranked:
                    draft_st, quick_st = self.responses.search_response(ranked, base, [], relax_info)
                    draft_st = f"Mình đổi sang loại {alt_types[0]} tương tự cho bạn:<br>" + draft_st
                    mem.pending_followup = None
                else:
                    mem.no_result_context["tried_actions"].append("similar_type")
                    next_alts = alt_types[1:]
                    draft_st = f"Không có {alt_types[0]} phù hợp. Bạn muốn thử {', '.join(next_alts) or 'loại khác'} không?"
                    if "studio" in next_alts:
                        quick_st = ["Studio", "Xem tất cả BĐS gần biển", "Xem căn hộ phù hợp nhất"]
                    else:
                        quick_st = [t.capitalize() for t in next_alts[:1]] + ["Xem căn hộ phù hợp nhất", "Tìm căn hộ 4 tỷ"]
                    ranked = []
                    mem.pending_followup = f"similar_type:{','.join(next_alts)}"
                st_a = {"intent": "refine_search", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft_st, st_a, ranked)
                return self.wrap(draft_st, st_a, ranked[:6], quick_st, start)
            else:
                draft_st = "Bạn muốn đổi sang nhà phố, đất nền hay phòng trọ?"
                self.memory.update(session_id, message, draft_st, {"intent": "general_bds", "entities": {}}, [])
                return self.wrap(draft_st, {"intent": "general_bds"}, [], ["Nhà phố", "Đất nền", "Phòng trọ"], start)

        if is_similar_prop:
            prop = mem.selected_property
            if prop and isinstance(prop, dict):
                base: Dict[str, Any] = {}
                if prop.get("loai"): base["property_type"] = prop["loai"]
                gia = prop.get("gia", 0)
                if gia:
                    base["price_min"] = int(gia * 0.8)
                    base["price_max"] = int(gia * 1.2)
                if prop.get("dia_chi"):
                    for loc_k in DA_NANG_NEARBY_DISTRICTS:
                        if loc_k in (prop["dia_chi"] or "").lower():
                            base["location"] = loc_k
                            break
                if prop.get("dien_tich"):
                    base["area_min"] = max(0, int(prop["dien_tich"] * 0.7))
                    base["area_max"] = int(prop["dien_tich"] * 1.3)
                ranked_s, relax_s = self.find_ranked_properties(base, "search_property", mem=mem)
                ranked_s = [r for r in ranked_s if r.get("id") != prop.get("id")]
                if ranked_s:
                    draft_sp, quick_sp = self.responses.search_response(ranked_s, base, [], relax_s)
                    draft_sp = f"Căn tương tự với <b>{prop.get('tieu_de','căn đã xem')}</b>:<br>" + draft_sp
                else:
                    draft_sp = f"Mình chưa tìm được căn nào tương tự hiện có. Bạn muốn nới ngân sách hoặc mở rộng khu vực không?"
                    quick_sp = ["Nới ngân sách 20%", "Tìm khu vực gần đó"]
                    ranked_s = []
                sp_a = {"intent": "refine_search", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft_sp, sp_a, ranked_s)
                return self.wrap(draft_sp, sp_a, ranked_s[:6], quick_sp, start)
            else:
                draft_sp = "Bạn cho mình biết căn muốn tìm tương tự là căn nào nhé?"
                self.memory.update(session_id, message, draft_sp, {"intent": "general_bds", "entities": {}}, [])
                return self.wrap(draft_sp, {"intent": "general_bds"}, [], [], start)
        
        # 5d. pending_followup: handle when user picks a suggested type (e.g. "Studio")
        pending = getattr(mem, 'pending_followup', None) or ""
        STUDIO_KEYWORDS = ["studio", "can studio", "can ho studio", "studio apartment"]
        is_studio = any(k in msg_ascii_lower for k in STUDIO_KEYWORDS)
        if is_studio or (pending.startswith("similar_type:") and any(k in msg_ascii_lower for k in [
                t.strip().lower().replace(" ", "") for t in pending.replace("similar_type:", "").split(",")
            ])):
            base_s = deepcopy(mem.last_no_result_filters or mem.last_search_filters or mem.entities or {})
            base_s["property_type"] = "căn hộ"
            base_s["title_keyword"] = "studio"  # used by fetch_properties if supported
            base_s["keyword"] = "studio"
            ranked_studio, ri_studio = self.find_ranked_properties(base_s, "search_property", mem=mem)
            # Also try a broader search if title_keyword not supported
            if not ranked_studio:
                base_s2 = deepcopy(base_s)
                base_s2.pop("title_keyword", None)
                base_s2.pop("keyword", None)
                all_rows = self.data.fetch_properties(base_s2, limit=20)
                ranked_studio = [r for r in all_rows if "studio" in (r.get("tieu_de") or "").lower()
                                 or "studio" in (r.get("mo_ta") or "").lower()]
                if ranked_studio:
                    ranked_studio = self.recommender.rank(ranked_studio, base_s2)
            mem.pending_followup = None  # clear after handling
            if ranked_studio:
                draft_stu, quick_stu = self.responses.search_response(ranked_studio, base_s, [], {"level": "exact"})
                draft_stu = f"Mình thử tìm căn hộ studio theo tiêu chí gần nhất cho bạn:<br>" + draft_stu
            else:
                mem.no_result_context["tried_actions"].append("studio")
                draft_stu = "Hiện dữ liệu chưa có studio phù hợp theo ngân sách/khu vực này. Mình gợi ý bạn xem các căn hộ phù hợp nhất hiện có hoặc xem tất cả BĐS gần biển."
                quick_stu = ["Xem căn hộ phù hợp nhất", "Xem tất cả BĐS gần biển", "Tìm căn hộ 4 tỷ"]
                ranked_studio = []
            stu_a = {"intent": "refine_search", "context": "search", "entities": base_s}
            self.memory.update(session_id, message, draft_stu, stu_a, ranked_studio)
            return self.wrap(draft_stu, stu_a, ranked_studio[:6], quick_stu, start)

        action_type = (payload.get("action") or {}).get("type") if isinstance(payload.get("action"), dict) else payload.get("action_type")
        
        rule = self.nlu.analyze(message, mem)
        if action_type == "search/refine":
            rule["intent"] = "refine_search"
            
        enhance_enabled = os.getenv("CHATBOT_LLM_ENHANCE", "false").lower() == "true"
        needs_llm = rule.get("intent") in ["empty", "general_bds", "out_of_domain"]
        gem = None
        if needs_llm or enhance_enabled:
            gem = self.gemini.analyze(message, mem)
            
        analysis = self.merge_analysis(rule, gem, mem)

        # --- Role policy: block intents not allowed for this actor ---
        policy_resp = RolePolicy.enforce(actor, analysis, mem)
        if policy_resp:
            policy_resp.setdefault("processing_time", round(time.time() - start, 3))
            return policy_resp

        entities = analysis.get("entities")
        if entities is None:
            entities = {}
            analysis["entities"] = entities

        # Ensure fast path rule-based reference resolution for ordinals/numbers
        resolved_item = resolve_property_reference(message, mem)
        if resolved_item:
            analysis["selected_property"] = resolved_item
            analysis["property_reference_resolved"] = True
            is_view_detail = any(k in msg_ascii for k in ["chi tiet", "xem can nay", "xem can do", "xem nha nay", "xem nha do"])
            wants_booking = not is_view_detail and (mem.active_context == "booking" or (mem.booking_state and mem.booking_state.get("status") == "collecting") or analysis.get("intent") == "appointment" or any(k in msg_ascii for k in ["dat lich", "hen", "tham quan", "chon", "xem nha"]))
            if wants_booking:
                analysis["intent"] = "appointment"
            else:
                analysis["intent"] = "property_detail"
                    
        # Property detail intent response
        if analysis.get("intent") == "property_detail" or action_type == "property/detail":
            if not analysis.get("selected_property"):
                if mem.selected_property:
                    analysis["selected_property"] = mem.selected_property
                elif getattr(mem, 'last_booked_property', None):
                    analysis["selected_property"] = mem.last_booked_property
            
        if analysis.get("intent") == "property_detail" and analysis.get("selected_property"):
            mem.selected_property = analysis.get("selected_property")
            draft, quick = self.responses.property_detail_response(mem.selected_property)
            self.memory.update(session_id, message, draft, analysis, [])
            return self.wrap(draft, analysis, [], quick, start)

        # Smart Contextual Fallback protection
        if analysis.get("intent") in ["out_of_domain", "empty", "general_bds"]:
            if mem.active_context == "booking" and mem.last_results:
                draft = "Bạn muốn chọn căn số mấy trong danh sách trên để đặt lịch?"
                self.memory.update(session_id, message, draft, analysis, [])
                return self.wrap(draft, analysis, [], ["Căn 1", "Căn 2", "Hủy"], start)
            elif mem.active_context == "search" and mem.last_results:
                draft = "Bạn muốn lọc theo giá, diện tích, khu vực hay chọn căn số mấy?"
                self.memory.update(session_id, message, draft, analysis, [])
                return self.wrap(draft, analysis, [], ["Rẻ hơn", "Rộng hơn", "Căn 1"], start)

        # Handle package_info intent directly
        if analysis.get("intent") == "package_info":
            print(
                "🎯 DETECTED package_info intent (merged) - calling handle_package_action"
            )
            package_payload = deepcopy(payload)
            requested_count = (analysis.get("entities") or {}).get(
                "requested_listing_count"
            )
            if requested_count is not None:
                package_payload["requested_listing_count"] = requested_count
            package_action = (
                "goi_tin/recommend_package"
                if requested_count is not None
                else "goi_tin/list_packages"
            )
            routed = self.router.handle_package_action(
                package_action, analysis, package_payload
            )
            routed.setdefault("intent", analysis.get("intent"))
            routed.setdefault("context", analysis.get("context"))
            routed.setdefault("processing_time", round(time.time() - start, 3))
            return routed

        # Handle other intents
        routed = self.router.route(message, payload, analysis, mem)
        if routed:
            routed.setdefault("intent", analysis.get("intent"))
            routed.setdefault("context", analysis.get("context"))
            routed.setdefault("processing_time", round(time.time() - start, 3))
            return routed

        # Fallback responses
        if mem.active_context == "valuation" and analysis.get("intent") in ["search_property", "general_bds", "empty", "refine_search"]:
            # Check if user is providing missing valuation info (area, type, location)
            prev_val_ents = mem.valuation_state.get("entities", {})
            merged_ents = deepcopy(prev_val_ents)
            cur_ents = analysis.get("entities", {})
            for k, v in cur_ents.items():
                if v:
                    merged_ents[k] = v
            analysis["entities"] = merged_ents
            analysis["intent"] = "valuation"
            
        if analysis.get("intent") == "valuation":
            mem.active_context = "valuation"
            ents = analysis.get("entities", {})
            # Merge with previous valuation_state entities if collecting
            if mem.valuation_state.get("status") == "collecting":
                prev_ents = mem.valuation_state.get("entities", {})
                for k, v in prev_ents.items():
                    if k not in ents or not ents[k]:
                        ents[k] = v
                analysis["entities"] = ents
            has_type = bool(ents.get("property_type"))
            has_loc = bool(ents.get("location") or ents.get("locations"))
            has_area = bool(ents.get("area_min") or ents.get("area_max"))
            if has_type and has_loc and has_area:
                # Reset valuation_state
                mem.valuation_state = {"status": "idle", "entities": {}}
                try:
                    ranked, _ = self.find_ranked_properties(ents, "valuation", mem=mem)
                except Exception as e:
                    logger.warning("valuation_find_fail | %s", e)
                    ranked = []
                if ranked:
                    avg_price = sum(r.get("gia", 0) for r in ranked) / len(ranked)
                    draft_val = f"Mình đã tìm {len(ranked)} tin đăng tương tự (cùng loại, khu vực) để ước tính. Giá tham khảo khoảng {money_vnd(avg_price)}.<br><i>Lưu ý: Đây chỉ là ước tính tự động, không phải thẩm định chính thức.</i>"
                else:
                    draft_val = "Hiện tại hệ thống chưa có đủ tin đăng tương tự ở khu vực này để đưa ra mức giá tham khảo chính xác."
                self.memory.update(session_id, message, draft_val, analysis, ranked)
                return self.wrap(draft_val, analysis, ranked[:6], ["Tìm BĐS cùng khu vực", "Đặt lịch xem nhà"], start)
            else:
                # Save partial info and ask for missing
                mem.valuation_state = {"status": "collecting", "entities": ents}
                if has_type and has_loc and not has_area:
                    prop_type = ents.get("property_type", "BĐS")
                    loc = ents.get("location") or (ents.get("locations") or ["khu vực này"])[0]
                    draft_val = f"Bạn cho mình biết diện tích {prop_type} ở {loc} khoảng bao nhiêu m² để ước tính sát hơn nhé."
                    qr_val = ["60m²", "100m²", "150m²", "200m²"]
                elif has_type and not has_loc:
                    prop_type = ents.get("property_type", "BĐS")
                    draft_val = f"Bạn muốn định giá {prop_type} ở khu vực nào tại Đà Nẵng?"
                    qr_val = ["Hải Châu", "Sơn Trà", "Ngũ Hành Sơn", "Liên Chiểu"]
                else:
                    draft_val = "Mình có thể hỗ trợ ước tính giá tham khảo. Bạn cho mình thêm vài thông tin nhé:<br>1. Loại BĐS: căn hộ/nhà phố/đất nền...<br>2. Khu vực: quận/phường hoặc địa chỉ gần đúng.<br>3. Diện tích."
                    qr_val = ["Căn hộ Hải Châu 60m²", "Nhà phố Sơn Trà 100m²", "Đất nền Liên Chiểu 150m²"]
                self.memory.update(session_id, message, draft_val, analysis, [])
                return self.wrap(draft_val, analysis, [], qr_val, start)

        if analysis.get("intent") in [
            "ending",
            "out_of_domain",
            "empty",
            "appointment",
            "general_bds",
            "posting_guide",
            "package_buy_guide",
        ]:
            if actor == "customer" and analysis.get("intent") in ["empty", "out_of_domain", "general_bds"]:
                draft = "Mình có thể giúp bạn tìm BĐS, lọc theo ngân sách/khu vực, xem chi tiết, định giá hoặc đặt lịch xem nhà. Bạn muốn tìm căn theo khu vực/ngân sách nào?"
                quick = ["Tìm căn hộ 4 tỷ", "Định giá BĐS", "Đặt lịch xem nhà"]
            else:
                draft, quick = self.responses.simple(analysis.get("intent"))
            if analysis.get("intent") == "ending":
                self.memory.reset(session_id, "conversation_ending")
            else:
                self.memory.update(session_id, message, draft, analysis, [])
            return self.wrap(draft, analysis, [], quick, start)

        # Default search flow
        entities_s = analysis.get("entities") or {}
        missing = self.missing_info(entities_s, analysis.get("intent"))
        ranked, relax_info = self.find_ranked_properties(
            entities_s, analysis.get("intent"), mem=mem, raw_text=normalize_text(message)
        )
        # Preserve no-result filters for later quick replies
        if not ranked and entities_s:
            mem.last_no_result_filters = deepcopy(entities_s)
        draft, quick = self.responses.search_response(
            ranked, entities_s, missing, relax_info
        )
        try:
            enhanced = self.gemini.enhance(draft, message, analysis)
        except Exception:
            enhanced = draft  # LLM failure must not break the result
        self.memory.update(session_id, message, enhanced, analysis, ranked)
        return self.wrap(enhanced, analysis, ranked[:6], quick, start)

    def find_ranked_properties(
        self,
        entities: Dict[str, Any],
        intent: str,
        mem: Optional[ChatMemory] = None,
        is_refinement: bool = False,
        raw_text: str = "",
    ) -> Tuple[List[Dict[str, Any]], Dict[str, Any]]:
        mem = mem or ChatMemory()
        if intent == "refine_search" or entities.get("refinement") or is_refinement:
            last_filters = deepcopy(mem.last_search_filters or mem.entities or {})
            refinement_type = entities.get("refinement_type") or last_filters.get("refinement_type")
            for k, v in last_filters.items():
                if k not in entities or entities[k] in [None, "", []]:
                    entities[k] = deepcopy(v)
            
            if refinement_type == "cheaper" or "rẻ hơn" in raw_text:
                entities["superlative"] = "cheapest"
            elif refinement_type == "larger" or "rộng hơn" in raw_text:
                entities["superlative"] = "largest"
                if mem.last_results:
                    areas = [float(r["dien_tich"]) for r in mem.last_results if r.get("dien_tich")]
                    if areas:
                        avg_area = sum(areas) / len(areas)
                        if not entities.get("area_min") or entities.get("area_min") < avg_area:
                            entities["area_min"] = int(avg_area)
            elif refinement_type == "near_beach" or "gần biển" in raw_text:
                lifestyles = entities.get("lifestyle") or []
                if "near_beach" not in lifestyles:
                    lifestyles.append("near_beach")
                entities["lifestyle"] = lifestyles
                if not entities.get("location") and not entities.get("locations"):
                    entities["locations"] = ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê"]
            elif refinement_type == "center" or "gần trung tâm" in raw_text:
                lifestyles = entities.get("lifestyle") or []
                if "center" not in lifestyles:
                    lifestyles.append("center")
                entities["lifestyle"] = lifestyles
                if not entities.get("location") and not entities.get("locations"):
                    entities["locations"] = ["hải châu", "thanh khê"]

        base = deepcopy(entities)
        rows = [
            r
            for r in self.data.fetch_properties(base, limit=18)
            if not self.data.is_dirty_listing(r)
        ]
        if rows:
            ranked = self.recommender.rank(rows, base)
            if entities.get("refinement_type") == "near_beach" or "gần biển" in raw_text:
                strict_ranked = [r for r in ranked if "gần biển" in r.get("reason", "")]
                if not strict_ranked:
                    if mem: mem.no_result_context["reason"] = "near_beach"
                    return [], {"level": "none", "message": "Mình chưa thấy căn hộ đúng tiêu chí gần biển. Bạn muốn thử nới ngân sách, mở rộng khu vực biển hoặc xem các căn phù hợp nhất hiện có không?"}
                return strict_ranked, {"level": "exact", "message": ""}
            return ranked, {"level": "exact", "message": ""}
        return [], {"level": "none", "message": ""}

    def missing_info(self, entities: Dict[str, Any], intent: str) -> List[str]:
        if intent == "refine_search":
            return []
        missing = []
        if not entities.get("location"):
            missing.append("location")
        if not entities.get("price_min") and not entities.get("price_max"):
            missing.append("price")
        if not entities.get("property_type"):
            missing.append("property_type")
        return missing[:2]

    # FIX 2: Protect package_info intent from being overridden
    def merge_analysis(
        self, rule: Dict[str, Any], gem: Optional[Dict[str, Any]], mem: ChatMemory
    ) -> Dict[str, Any]:
        analysis = deepcopy(rule)
        rule_intent = rule.get("intent")

        PROTECTED_INTENTS = {
            "package_info",
            "valuation",
            "ending",
            "appointment",
            "refine_search",
            "search_property",
            "posting_guide",
            "package_buy_guide",
        }

        if gem and isinstance(gem, dict):
            intent = gem.get("intent") or rule_intent

            # Prevent LLM from overriding critical rule-based intents
            if rule_intent in PROTECTED_INTENTS:
                intent = rule_intent
                logger.info(
                    f"merge_analysis | protected_intent={rule_intent} | ignoring_gem={gem.get('intent')}"
                )

            entities = {**(gem.get("entities") or {}), **(rule.get("entities") or {})}
            analysis = {
                "intent": intent,
                "context": gem.get("context") or rule.get("context"),
                "entities": entities,
                "confidence": gem.get("confidence", 0.8),
            }
        return analysis

    def wrap(
        self,
        response: str,
        analysis: Dict[str, Any],
        suggestions: List[Dict[str, Any]],
        quick: List[str],
        start: float,
    ) -> Dict[str, Any]:
        payload = {
            "success": True,
            "response": response,
            "intent": analysis.get("intent"),
            "context": analysis.get("context"),
            "suggestions": suggestions,
            "quick_replies": quick,
            "processing_time": round(time.time() - start, 3),
            "version": APP_VERSION,
        }
        if analysis.get("booking_request"):
            payload["booking_request"] = analysis.get("booking_request")
        return payload


chatbot = BDSChatbot()
app = Flask(__name__)
CORS(app)


@app.route("/", methods=["GET"])
def health():
    return jsonify(
        {"success": True, "service": "AI Real Estate Assistant", "version": APP_VERSION}
    )


@app.route("/chat", methods=["POST"])
def chat():
    payload = request.get_json(silent=True) or {}
    session_id = payload.get("session_id", "unknown")
    message    = payload.get("message", "")
    actor      = payload.get("actor", "guest")
    try:
        result = chatbot.process(payload)
        return jsonify(result)
    except Exception as exc:
        # Log full state for diagnosis
        try:
            mem = chatbot.memory.get(session_id)
            logger.exception(
                "chat_process_fail | session=%s | msg=%s | active_ctx=%s | last_intent=%s"
                " | last_results_len=%d | sel_prop=%s | last_filters=%s | no_result_filters=%s"
                " | pending=%s | err=%s",
                session_id, message,
                getattr(mem, "active_context", None),
                getattr(mem, "last_intent", None),
                len(getattr(mem, "last_results", []) or []),
                (mem.selected_property or {}).get("id") if getattr(mem, "selected_property", None) else None,
                getattr(mem, "last_search_filters", {}),
                getattr(mem, "last_no_result_filters", {}),
                getattr(mem, "pending_followup", None),
                exc,
            )
        except Exception:
            logger.exception("chat_process_fail | session=%s | msg=%s | err=%s", session_id, message, exc)
        return jsonify(
            {
                "success": True,
                "response": "Mình chưa truy vấn được dữ liệu BĐS lúc này, bạn thử lại sau nhé.",
                "intent": "fallback",
                "context": None,
                "suggestions": [],
                "quick_replies": ["Tìm BĐS", "Định giá", "Gói tin"],
                "processing_time": 0,
            }
        )


@app.route("/loai-bds", methods=["GET"])
def loai_bds():
    return jsonify({"success": True, "data": chatbot.data.list_types()})


if __name__ == "__main__":
    app.run(
        host=os.getenv("BDS_SERVICE_HOST", "0.0.0.0"),
        port=int(os.getenv("BDS_SERVICE_PORT", "5002")),
        debug=os.getenv("FLASK_ENV") == "development",
    )
