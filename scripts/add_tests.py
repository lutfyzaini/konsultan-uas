import os

BASE = r"postman/collections/E-Konsul API Collection"

FILES = {
    r"Categories & Experts/Daftar Semua Kategori.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah array", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("array");
    });

    pm.test("Setiap kategori punya id dan name", function () {
        var json = pm.response.json();
        json.data.forEach(function(item) {
            pm.expect(item).to.have.property("id");
            pm.expect(item).to.have.property("name");
        });
    });
""",
    r"Categories & Experts/Daftar Semua Expert (Approved).request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah array", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("array");
    });

    pm.test("Setiap expert punya id, title, dan verification_status", function () {
        var json = pm.response.json();
        json.data.forEach(function(item) {
            pm.expect(item).to.have.property("id");
            pm.expect(item).to.have.property("title");
            pm.expect(item).to.have.property("verification_status");
        });
    });
""",
    r"Categories & Experts/Detail Satu Expert.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah object", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("object");
    });

    pm.test("Expert punya id, title, hourly_rate, verification_status", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("id");
        pm.expect(json.data).to.have.property("title");
        pm.expect(json.data).to.have.property("hourly_rate");
        pm.expect(json.data).to.have.property("verification_status");
    });
""",
    r"Slots/Daftar Slot Ketersediaan Pakar.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah array", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("array");
    });

    pm.test("Setiap slot punya id, day_of_week, start_time, end_time", function () {
        var json = pm.response.json();
        json.data.forEach(function(item) {
            pm.expect(item).to.have.property("id");
            pm.expect(item).to.have.property("day_of_week");
            pm.expect(item).to.have.property("start_time");
            pm.expect(item).to.have.property("end_time");
        });
    });
""",
    r"Slots/Buat Slot Ketersediaan Baru.request.yaml": r"""tests:
  script: |-
    pm.test("Status 201 Created", function () {
        pm.response.to.have.status(201);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah object", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("object");
    });

    pm.test("Slot baru punya id, day_of_week, start_time, end_time", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("id");
        pm.expect(json.data).to.have.property("day_of_week");
        pm.expect(json.data).to.have.property("start_time");
        pm.expect(json.data).to.have.property("end_time");
    });
""",
    r"Slots/Hapus Slot Ketersediaan.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });
""",
    r"Bookings/Daftar Booking.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah array", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("array");
    });

    pm.test("Setiap booking punya id, status, total_price, booking_type", function () {
        var json = pm.response.json();
        json.data.forEach(function(item) {
            pm.expect(item).to.have.property("id");
            pm.expect(item).to.have.property("status");
            pm.expect(item).to.have.property("total_price");
            pm.expect(item).to.have.property("booking_type");
        });
    });
""",
    r"Bookings/Buat Booking (Kunci Slot).request.yaml": r"""tests:
  script: |-
    pm.test("Status 201 Created", function () {
        pm.response.to.have.status(201);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah object", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("object");
    });

    pm.test("Booking baru punya id, status, total_price", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("id");
        pm.expect(json.data).to.have.property("status");
        pm.expect(json.data).to.have.property("total_price");
    });
""",
    r"Bookings/Detail Booking.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah object", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("object");
    });

    pm.test("Booking punya id, client_id, expert_profile_id, status", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("id");
        pm.expect(json.data).to.have.property("client_id");
        pm.expect(json.data).to.have.property("expert_profile_id");
        pm.expect(json.data).to.have.property("status");
    });
""",
    r"Bookings/Batalkan Booking.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Booking status berubah menjadi cancelled", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("status");
        pm.expect(json.data.status).to.equal("cancelled");
    });
""",
    r"Consultations & Chats/Detail Konsultasi & Riwayat Chat.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah object", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("object");
    });

    pm.test("Konsultasi punya id, booking_id, status", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("id");
        pm.expect(json.data).to.have.property("booking_id");
        pm.expect(json.data).to.have.property("status");
    });
""",
    r"Consultations & Chats/Kirim Pesan Chat Baru.request.yaml": r"""tests:
  script: |-
    pm.test("Status 201 Created", function () {
        pm.response.to.have.status(201);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data adalah object", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.be.an("object");
    });

    pm.test("Pesan punya id, sender_id, message", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("id");
        pm.expect(json.data).to.have.property("sender_id");
        pm.expect(json.data).to.have.property("message");
    });
""",
    r"Consultations & Chats/Status Konsultasi & Polling Pesan.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Field data punya consultation_status", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("consultation_status");
    });

    pm.test("Field data punya messages (array)", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("messages");
        pm.expect(json.data.messages).to.be.an("array");
    });
""",
    r"Consultations & Chats/Akhiri Sesi Konsultasi.request.yaml": r"""tests:
  script: |-
    pm.test("Status 200 OK", function () {
        pm.response.to.have.status(200);
    });

    pm.test("Response adalah JSON", function () {
        pm.response.to.be.json;
    });

    pm.test("Response punya field success = true", function () {
        var json = pm.response.json();
        pm.expect(json.success).to.be.true;
    });

    pm.test("Konsultasi berhasil diakhiri", function () {
        var json = pm.response.json();
        pm.expect(json.data).to.have.property("status");
        pm.expect(json.data.status).to.equal("ended");
    });
""",
}

for rel_path, tests_block in FILES.items():
    full_path = os.path.join(BASE, rel_path)
    with open(full_path, 'rb') as f:
        raw = f.read()

    # Detect line ending
    if b'\r\n' in raw:
        le = b'\r\n'
    else:
        le = b'\n'

    content = raw.decode('utf-8')

    # Find the last 'order:' line and insert tests block before it
    lines = content.splitlines()
    insert_idx = None
    for i, line in enumerate(lines):
        if line.strip().startswith('order:'):
            insert_idx = i
            break

    if insert_idx is None:
        print(f"SKIP (no order:): {rel_path}")
        continue

    if 'tests:' in content:
        print(f"SKIP (already has tests:): {rel_path}")
        continue

    # Build tests lines
    tests_lines = tests_block.rstrip('\n').splitlines()

    new_lines = lines[:insert_idx] + tests_lines + [lines[insert_idx]]
    new_content = (le.decode('utf-8')).join(new_lines) + le.decode('utf-8')

    with open(full_path, 'wb') as f:
        f.write(new_content.encode('utf-8'))

    print(f"OK: {rel_path}")

print("Done.")
