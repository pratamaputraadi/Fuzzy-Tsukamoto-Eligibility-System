import numpy as np
import sys
import json
import base64

def fuzzy_low(value, min_val, max_val):
    if value <= min_val:
        return 1.0
    elif value >= max_val:
        return 0.0
    elif min_val == max_val:
        return 1.0 if value <= min_val else 0.0
    else:
        return (max_val - value) / (max_val - min_val)

def fuzzy_high(value, min_val, max_val):
    if value <= min_val:
        return 0.0
    elif value >= max_val:
        return 1.0
    elif min_val == max_val:
        return 0.0 if value <= min_val else 1.0
    else:
        return (value - min_val) / (max_val - min_val)

param_jurusan = {
    "TKJ": {"nama": "Teknik Komputer dan Jaringan", "threshold": 70},
    "RPL": {"nama": "Rekayasa Perangkat Lunak", "threshold": 75},
    "MM": {"nama": "Multimedia", "threshold": 65},
    "AKL": {"nama": "Akuntansi dan Keuangan Lembaga", "threshold": 70},
    "OTKP": {"nama": "Otomatisasi dan Tata Kelola Perkantoran", "threshold": 65},
    "BDP": {"nama": "Bisnis Daring dan Pemasaran", "threshold": 65},
    "TKRO": {"nama": "Teknik Kendaraan Ringan Otomotif", "threshold": 60},
    "TBSM": {"nama": "Teknik dan Bisnis Sepeda Motor", "threshold": 60},
    "PH": {"nama": "Perhotelan", "threshold": 65},
    "TBG": {"nama": "Tata Boga", "threshold": 60},
    "TBS": {"nama": "Tata Busana", "threshold": 60},
    "FARM": {"nama": "Farmasi", "threshold": 70},
}

subject_criteria = {
    "TKJ": {
        "unggulan": {"Matematika": 85, "Informatika": 88, "Bahasa Inggris": 80},
        "non_unggulan": {"Bahasa Indonesia": 72, "Fisika": 68, "Kimia": 65, "Biologi": 66, "PPKn": 70, "IPS": 69, "Sosiologi": 67, "Prakarya": 68, "Seni Budaya": 65}
    },
    "RPL": {
        "unggulan": {"Informatika": 90, "Matematika": 85, "Bahasa Indonesia": 78},
        "non_unggulan": {"Bahasa Inggris": 74, "Fisika": 67, "Kimia": 66, "Biologi": 65, "PPKn": 70, "IPS": 68, "Sosiologi": 66, "Prakarya": 69, "Seni Budaya": 65}
    },
    "MM": {
        "unggulan": {"Seni Budaya": 85, "Informatika": 80, "Bahasa Inggris": 78},
        "non_unggulan": {"Matematika": 70, "Bahasa Indonesia": 72, "Fisika": 66, "Kimia": 65, "Biologi": 67, "PPKn": 69, "IPS": 68, "Sosiologi": 66, "Prakarya": 70}
    },
    "AKL": {
        "unggulan": {"Matematika": 88, "IPS": 85, "Bahasa Indonesia": 80},
        "non_unggulan": {"Bahasa Inggris": 72, "Informatika": 70, "Fisika": 65, "Kimia": 66, "Biologi": 67, "PPKn": 69, "Sosiologi": 68, "Prakarya": 70, "Seni Budaya": 65}
    },
    "OTKP": {
        "unggulan": {"Bahasa Indonesia": 82, "IPS": 80, "Informatika": 78},
        "non_unggulan": {"Matematika": 70, "Bahasa Inggris": 72, "Fisika": 65, "Kimia": 65, "Biologi": 66, "PPKn": 69, "Sosiologi": 68, "Prakarya": 67, "Seni Budaya": 65}
    },
    "BDP": {
        "unggulan": {"IPS": 85, "Bahasa Inggris": 80, "Informatika": 78},
        "non_unggulan": {"Matematika": 70, "Bahasa Indonesia": 72, "Fisika": 65, "Kimia": 65, "Biologi": 66, "PPKn": 69, "Sosiologi": 68, "Prakarya": 67, "Seni Budaya": 65}
    },
    "TKRO": {
        "unggulan": {"Fisika": 82, "Matematika": 80, "Prakarya": 85},
        "non_unggulan": {"Bahasa Indonesia": 70, "Bahasa Inggris": 72, "Informatika": 68, "Kimia": 65, "Biologi": 66, "PPKn": 69, "IPS": 68, "Sosiologi": 67, "Seni Budaya": 65}
    },
    "TBSM": {
        "unggulan": {"Prakarya": 85, "Fisika": 80, "Matematika": 78},
        "non_unggulan": {"Bahasa Indonesia": 70, "Bahasa Inggris": 72, "Informatika": 68, "Kimia": 65, "Biologi": 66, "PPKn": 69, "IPS": 68, "Sosiologi": 67, "Seni Budaya": 65}
    },
    "PH": {
        "unggulan": {"Bahasa Inggris": 85, "Seni Budaya": 80, "IPS": 78},
        "non_unggulan": {"Matematika": 70, "Bahasa Indonesia": 72, "Fisika": 65, "Kimia": 65, "Biologi": 66, "PPKn": 69, "Sosiologi": 68, "Prakarya": 67, "Informatika": 60}
    },
    "TBG": {
        "unggulan": {"Prakarya": 85, "Biologi": 75, "Bahasa Indonesia": 78},
        "non_unggulan": {"Bahasa Inggris": 70, "Matematika": 68, "Fisika": 65, "Kimia": 65, "PPKn": 69, "IPS": 68, "Sosiologi": 67, "Seni Budaya": 65, "Informatika": 60}
    },
    "TBS": {
        "unggulan": {"Seni Budaya": 85, "Prakarya": 82, "Bahasa Indonesia": 75},
        "non_unggulan": {"Bahasa Inggris": 70, "Matematika": 68, "Fisika": 65, "Kimia": 65, "Biologi": 66, "PPKn": 69, "IPS": 68, "Sosiologi": 67, "Informatika": 60}
    },
    "FARM": {
        "unggulan": {"Biologi": 88, "Kimia": 85, "Matematika": 80},
        "non_unggulan": {"Bahasa Indonesia": 72, "Bahasa Inggris": 70, "Fisika": 68, "PPKn": 69, "IPS": 67, "Sosiologi": 66, "Prakarya": 65, "Seni Budaya": 65, "Informatika": 60}
    }
}

def get_fuzzy_range(jurusan_kode):
    jurusan_ranges = {
        "RPL": {"akademik": (70, 95), "tinggi": (160, 185), "berat": (50, 75)},
        "TKJ": {"akademik": (65, 90), "tinggi": (155, 180), "berat": (50, 70)},
        "MM": {"akademik": (60, 90), "tinggi": (150, 175), "berat": (45, 65)},
        "AKL": {"akademik": (65, 90), "tinggi": (150, 175), "berat": (45, 65)},
        "OTKP": {"akademik": (60, 85), "tinggi": (150, 175), "berat": (45, 65)},
        "BDP": {"akademik": (60, 85), "tinggi": (150, 175), "berat": (45, 65)},
        "TKRO": {"akademik": (50, 80), "tinggi": (155, 180), "berat": (55, 80)},
        "TBSM": {"akademik": (50, 80), "tinggi": (155, 180), "berat": (55, 80)},
        "PH": {"akademik": (60, 85), "tinggi": (155, 180), "berat": (45, 70)},
        "TBG": {"akademik": (55, 85), "tinggi": (150, 175), "berat": (45, 70)},
        "TBS": {"akademik": (55, 85), "tinggi": (150, 175), "berat": (45, 70)},
        "FARM": {"akademik": (70, 95), "tinggi": (150, 175), "berat": (45, 65)},
    }
    return jurusan_ranges.get(jurusan_kode, {
        "akademik": (50, 80), "tinggi": (140, 190), "berat": (40, 90)
    })

def calculate_academic_score(student_scores, kriteria_jurusan_mapel):
    score_unggulan = 0
    jumlah_unggulan_terpenuhi = 0
    mapel_unggulan_kriteria = kriteria_jurusan_mapel.get("unggulan", {})
    total_mapel_unggulan = len(mapel_unggulan_kriteria)

    if total_mapel_unggulan > 0:
        for mapel, min_nilai in mapel_unggulan_kriteria.items():
            if student_scores.get(mapel, 0) >= min_nilai:
                jumlah_unggulan_terpenuhi += 1
        
        if jumlah_unggulan_terpenuhi == total_mapel_unggulan:
            score_unggulan = 60
        else:
            return 0
    else:
        score_unggulan = 60

    score_non_unggulan = 0
    mapel_non_unggulan_kriteria = kriteria_jurusan_mapel.get("non_unggulan", {})
    total_mapel_non_unggulan = len(mapel_non_unggulan_kriteria)
    jumlah_non_unggulan_terpenuhi = 0
    
    if total_mapel_non_unggulan > 0:
        for mapel, min_nilai in mapel_non_unggulan_kriteria.items():
            if student_scores.get(mapel, 0) >= min_nilai:
                jumlah_non_unggulan_terpenuhi += 1
        score_non_unggulan = (jumlah_non_unggulan_terpenuhi / total_mapel_non_unggulan) * 40
    else:
        score_non_unggulan = 40
        
    return score_unggulan + score_non_unggulan

def hitung_tsukamoto(student_all_scores, tinggi, berat):
    hasil = {}

    for kode_jur, jur_info in param_jurusan.items():
        current_fuzzy_range = get_fuzzy_range(kode_jur)
        kriteria_mapel_jurusan = subject_criteria.get(kode_jur, {"unggulan": {}, "non_unggulan": {}})
        
        akademik_score_val = calculate_academic_score(student_all_scores, kriteria_mapel_jurusan)

        def calc_derajat(label_param_calc, value_param_calc, range_to_use):
            param_range_calc = sorted(range_to_use)
            low_val = fuzzy_low(value_param_calc, *param_range_calc)
            high_val = fuzzy_high(value_param_calc, *param_range_calc)
            return low_val, high_val

        detail = {
            "akademik": {"nilai": round(akademik_score_val,2), "range": current_fuzzy_range["akademik"]},
            "tinggi": {"nilai": tinggi, "range": current_fuzzy_range["tinggi"]},
            "berat": {"nilai": berat, "range": current_fuzzy_range["berat"]},
            "rules": {}
        }
        
        akademik_low, akademik_high = calc_derajat("akademik", akademik_score_val, current_fuzzy_range["akademik"])
        detail["akademik"]["low"] = round(akademik_low, 2)
        detail["akademik"]["high"] = round(akademik_high, 2)

        tinggi_low, tinggi_high = calc_derajat("tinggi", tinggi, current_fuzzy_range["tinggi"])
        detail["tinggi"]["low"] = round(tinggi_low, 2)
        detail["tinggi"]["high"] = round(tinggi_high, 2)

        berat_low, berat_high = calc_derajat("berat", berat, current_fuzzy_range["berat"])
        detail["berat"]["low"] = round(berat_low, 2)
        detail["berat"]["high"] = round(berat_high, 2)

        rules_applied = []
        alpha1 = detail["akademik"]["high"]
        z1 = 50 + 50 * alpha1
        rules_applied.append((alpha1, z1))
        detail["rules"]["rule_1"] = {"alpha": round(alpha1, 3), "z": round(z1, 2), "formula": "z1 = 50 + 50 * alpha1"}

        alpha2 = detail["akademik"]["low"]
        z2_corrected = 50 - 40 * alpha2
        rules_applied.append((alpha2, z2_corrected))
        detail["rules"]["rule_2"] = {"alpha": round(alpha2, 3), "z": round(z2_corrected, 2), "formula": "z2 = 50 - 40 * alpha2 (corrected)"}

        alpha3 = min(detail["tinggi"]["high"], detail["berat"]["high"])
        z3 = 50 + 30 * alpha3
        rules_applied.append((alpha3, z3))
        detail["rules"]["rule_3"] = {"alpha": round(alpha3, 3), "z": round(z3, 2), "formula": "z3 = 50 + 30 * alpha3"}
        
        alpha4 = (detail["tinggi"]["low"] + detail["berat"]["low"]) / 2
        z4_corrected = 50 - 30 * alpha4
        rules_applied.append((alpha4, z4_corrected))
        detail["rules"]["rule_4"] = {"alpha": round(alpha4, 3), "z": round(z4_corrected, 2), "formula": "z4 = 50 - 30 * alpha4 (corrected)"}
        
        total_alpha_z = sum(a * z_val for a, z_val in rules_applied)
        total_alpha = sum(a for a, _ in rules_applied)
        final_score = total_alpha_z / total_alpha if total_alpha != 0 else 0.0
        final_score = max(0, min(final_score, 100))

        hasil[kode_jur] = {
            "jurusan": jur_info["nama"],
            "skor": round(final_score, 2),
            "threshold": jur_info["threshold"],
            "status": "Diterima" if final_score >= jur_info["threshold"] else "Tidak Diterima",
            "detail_hitungan": detail,
            "range_fuzzy_jurusan_digunakan": current_fuzzy_range,
            "kriteria_mapel_jurusan": kriteria_mapel_jurusan
        }
    return hasil

if __name__ == "__main__":
    if len(sys.argv) > 1:
        try:
            base64_encoded_json_arg = sys.argv[1]
            decoded_json_string = base64.b64decode(base64_encoded_json_arg).decode('utf-8')
            input_data = json.loads(decoded_json_string)

            student_scores = input_data.get("scores")
            student_height = input_data.get("height")
            student_weight = input_data.get("weight")

            if not isinstance(student_scores, dict) or student_height is None or student_weight is None:
                error_msg = {"error": "Invalid input structure after B64 decode: 'scores' must be a dictionary, 'height' and 'weight' must be provided."}
                print(json.dumps(error_msg, ensure_ascii=False))
                sys.exit(1)
            
            try:
                student_height_numeric = float(student_height)
                student_weight_numeric = float(student_weight)
                for mapel_key in student_scores:
                    if not (isinstance(student_scores[mapel_key], (int, float)) and 0 <= student_scores[mapel_key] <= 100):
                        raise ValueError(f"Nilai mapel '{mapel_key}' tidak valid: {student_scores[mapel_key]}. Harus numerik antara 0-100.")
            except ValueError as ve:
                error_msg = {"error": f"Invalid data type or value: {str(ve)}"}
                print(json.dumps(error_msg, ensure_ascii=False))
                sys.exit(1)

            hasil_penilaian_semua_jurusan = hitung_tsukamoto(student_scores, student_height_numeric, student_weight_numeric)
            print(json.dumps(hasil_penilaian_semua_jurusan, indent=4, ensure_ascii=False))

        except base64.BinasciiError:
            print(json.dumps({"error": "Invalid Base64 input."}, ensure_ascii=False))
            sys.exit(1)
        except UnicodeDecodeError:
            print(json.dumps({"error": "Failed to decode Base64 content to UTF-8 string."}, ensure_ascii=False))
            sys.exit(1)
        except json.JSONDecodeError:
            print(json.dumps({"error": "Invalid JSON input after Base64 decode."}, ensure_ascii=False))
            sys.exit(1)
        except Exception as e:
            print(json.dumps({"error": f"Error during fuzzy calculation: {str(e)}"}, ensure_ascii=False))
            sys.exit(1)
    else:
        info_msg = {
            "message": "No input data provided. Expected Base64 encoded JSON string as a command line argument.",
            "example_json_structure_before_base64": {
                "scores": {"Matematika": 80, "Informatika": 85, "Bahasa Inggris": 75, "Bahasa Indonesia": 70, "Fisika": 60, "Kimia": 60, "Biologi": 60, "PPKn": 70, "IPS": 70, "Sosiologi": 65, "Prakarya": 70, "Seni Budaya": 65},
                "height": 170,
                "weight": 60
            }
        }
        print(json.dumps(info_msg, indent=4, ensure_ascii=False))
        sys.exit(1)