# Site Yönetim Sistemi

> Kocaeli Üniversitesi - Bilişim Sistemleri Mühendisliği  
> TBL331: Veritabanı Yönetim Sistemleri | 2025-2026 Bahar Dönemi Projesi

---

## 1. Proje Özeti (Project Overview)

Bu proje, apartman ve site komplekslerinin yönetimini dijital ortamda kolaylaştırmak amacıyla geliştirilmiş kapsamlı bir web uygulamasıdır. Sistem; blok, daire, sakin, aidat, ödeme, şikayet ve gider yönetimi modüllerini tek bir platformda birleştirir. Veritabanı tarafında ilişkisel yapı, trigger, view, stored procedure ve index kullanılarak güçlü ve ölçeklenebilir bir mimari oluşturulmuştur.

### 1.1 Problem Tanımı

Günümüzde apartman ve site yöneticileri aidat takibi, sakin bilgileri, şikayet yönetimi ve gider takibi gibi işlemleri genellikle kağıt üzerinde veya dağınık Excel dosyalarında yapmaktadır. Bu durum:
- Veri kayıplarına ve hatalara yol açar
- Şeffaflık eksikliği oluşturur
- Aidat tahsilat süreçlerini yavaşlatır
- Şikayet ve taleplerin takibini zorlaştırır

Bu proje, yukarıdaki sorunlara çözüm olarak merkezi bir veritabanı ve kullanıcı dostu bir web arayüzü sunmaktadır.

### 1.2 Kullanılan Teknolojiler

| Katman | Teknoloji |
|--------|-----------|
| **Backend** | PHP 8.x (Prosedürel) |
| **Veritabanı** | MySQL 8.0 (PDO ile bağlantı) |
| **Sunucu** | Apache (XAMPP) |
| **Frontend** | Vanilla HTML5, CSS3, JavaScript |
| **CSS Framework** | Özel tasarım (Inline + External CSS) |
| **İkonlar** | Font Awesome 6.5.1 |

---

## 2. Geliştirme Ortamı ve Kurulum

### 2.1 Gereksinimler

- **XAMPP** (Apache + MySQL + PHP)
- **PHP** >= 8.0
- **MySQL** >= 8.0
- **Web Tarayıcısı** (Chrome, Firefox, Edge)

### 2.2 Kurulum Adımları

1. **XAMPP İndirme ve Kurulum**
   - [https://www.apachefriends.org](https://www.apachefriends.org) adresinden XAMPP indirin.
   - Kurulum sırasında Apache ve MySQL modüllerini seçin.

2. **Projeyi Kopyalama**
   - Bu proje dosyalarını `C:\xampp\htdocs\site_yonetim` klasörüne kopyalayın.

3. **Veritabanını Oluşturma**
   - XAMPP Control Panel'den **MySQL** ve **Apache** servislerini başlatın.
   - Tarayıcınızdan `http://localhost/phpmyadmin` adresine gidin.
   - Yeni bir veritabanı oluşturun: `site_yonetim`
   - İçe aktar (Import) sekmesinden `site_yonetim_schema.sql` dosyasını seçin ve çalıştırın.
   - Bu dosya; tüm tabloları, dummy data'yı, index'leri, view'ları, trigger'ları ve stored procedure'leri içerir.

4. **Alternatif Kurulum (Tam Kurulum Scripti)**
   - Tarayıcınızdan `http://localhost/site_yonetim/install_db.php` adresine gidin.
   - Bu script tüm tabloları, view'ları, trigger'ları ve stored procedure'leri otomatik oluşturur.
   - Admin şifresi: `admin` / `admin123`
   - Kurulum sonrası `install_db.php` dosyasını silin.

5. **Uygulamaya Giriş**
   - Admin Paneli: `http://localhost/site_yonetim/login.php`
   - **Varsayılan Admin:** `admin` / `admin123`
   - Sakin Paneli: Aynı giriş sayfasından sakin kullanıcı bilgileriyle giriş yapılabilir.

### 2.3 Proje Yapısı

```
site_yonetim/
│
├── index.php                   # Admin Dashboard
├── blocks.php                  # Blok listesi
├── apartments.php              # Daire listesi
├── residents.php               # Sakin listesi
├── dues.php                    # Aidat listesi
├── payments.php                # Ödeme listesi
├── pending_payments.php        # Bekleyen ödemeler
├── complaints.php              # Şikayet listesi
├── expenses.php                # Gider listesi
├── add_block.php               # Blok ekleme
├── add_apartment.php           # Daire ekleme
├── add_resident.php            # Sakin ekleme
├── add_due.php                 # Aidat ekleme
├── add_payment.php             # Ödeme ekleme
├── add_complaint.php           # Şikayet ekleme
├── add_expense.php             # Gider ekleme
├── login.php                   # Giriş sayfası
├── logout.php                  # Çıkış
├── register.php                # Sakin kullanıcı oluşturma
├── setup.php                   # Kurulum scripti
│
├── resident/                   # Sakin paneli
│   ├── index.php               # Sakin Dashboard
│   ├── my_dues.php             # Aidatlarım
│   ├── pay_dues.php            # Aidat öde (kart simülasyonu)
│   ├── my_complaints.php       # Şikayetlerim
│   ├── add_complaint.php       # Şikayet ekle
│   └── expenses.php            # Site giderleri
│
├── includes/                   # Ortak bileşenler
│   ├── db.php                  # Veritabanı bağlantısı
│   ├── auth.php                # Kimlik doğrulama fonksiyonları
│   ├── functions.php           # Global yardımcı fonksiyonlar
│   ├── header.php              # HTML head bileşeni
│   ├── header_admin.php        # Admin header
│   ├── header_resident.php     # Sakin header
│   ├── footer.php              # Footer
│   ├── footer_admin.php        # Admin footer
│   ├── footer_resident.php     # Sakin footer
│   ├── sidebar.php             # Sidebar
│   ├── sidebar_admin.php       # Admin sidebar
│   └── sidebar_resident.php    # Sakin sidebar
│
├── assets/
│   ├── css/
│   │   └── style.css           # Global stylesheet
│   └── js/
│       └── main.js             # JavaScript dosyası
│
├── site_yonetim_schema.sql     # Tam veritabanı şeması (tablolar + dummy data + index + view + trigger + procedure)
├── CONTEXT7.md                 # Proje analiz dokümanı
└── README.md                   # Bu dosya
```

---

## 3. Yazılım Mimarisi

### 3.1 Mimari Desen

Proje **Prosedürel PHP** mimarisi kullanmaktadır. Her sayfa kendi içinde MVC benzeri bir yapı izler:

- **Model (Veri Katmanı):** `includes/db.php` üzerinden PDO ile veritabanı işlemleri
- **View (Sunum Katmanı):** PHP içinde HTML/CSS ile arayüz
- **Controller (İş Mantığı):** Her sayfanın üst kısmındaki PHP kodları

### 3.2 Kimlik Doğrulama Mimarisi

- **Session tabanlı** kimlik doğrulama
- **Rol tabanlı yetkilendirme:** `admin` ve `resident`
- **Password hashing:** `password_hash()` ve `password_verify()`
- **Middleware fonksiyonları:** `requireAdmin()`, `requireResident()`, `requireLogin()`

### 3.3 Güvenlik Önlemleri

- PDO prepared statements ile SQL Injection koruması
- `htmlspecialchars()` ile XSS koruması
- Session hijacking koruması
- Password hashing (bcrypt)
- Role-based access control (RBAC)

---

## 4. Akış Şeması (Flow Diagram)

```
┌─────────────────┐
│   Ziyaretçi     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   login.php     │
│  (Giriş Ekranı) │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
┌────────┐ ┌──────────┐
│ Admin  │ │ Sakin    │
│ Girişi │ │ Girişi   │
└───┬────┘ └────┬─────┘
    │           │
    ▼           ▼
┌─────────────────┐     ┌─────────────────┐
│ index.php       │     │ resident/       │
│ (Admin Panel)   │     │ index.php       │
│                 │     │ (Sakin Panel)   │
│ • Dashboard     │     │                 │
│ • Bloklar       │     │ • Aidatlarım    │
│ • Daireler      │     │ • Aidat Öde     │
│ • Sakinler      │     │ • Şikayetlerim  │
│ • Aidatlar      │     │ • Giderler      │
│ • Ödemeler      │     │                 │
│ • Şikayetler    │     │                 │
│ • Giderler      │     │                 │
│ • Kullanıcılar  │     │                 │
└─────────────────┘     └─────────────────┘
         │                       │
         └───────────┬───────────┘
                     │
                     ▼
            ┌─────────────────┐
            │   MySQL DB      │
            │                 │
            │ • blocks        │
            │ • apartments    │
            │ • residents     │
            │ • dues          │
            │ • payments      │
            │ • complaints    │
            │ • expenses      │
            │ • users         │
            │                 │
            │ + Views         │
            │ + Triggers      │
            │ + Procedures    │
            │ + Indexes       │
            └─────────────────┘
```

---

## 5. Veritabanı Diyagramı (ER Diagram)

```
┌─────────────────┐       ┌─────────────────┐
│     blocks      │       │    expenses     │
├─────────────────┤       ├─────────────────┤
│ PK block_id     │       │ PK expense_id   │
│    block_name   │       │    expense_type │
│    created_at   │       │    amount       │
└────────┬────────┘       │    expense_date │
         │ 1:N            │    description  │
         │                │    created_at   │
         ▼                └─────────────────┘
┌─────────────────┐
│   apartments    │       ┌─────────────────┐
├─────────────────┤       │    residents    │
│ PK apartment_id │       ├─────────────────┤
│ FK block_id     │◄──────│ PK resident_id  │
│    floor_no     │ 1:1   │ FK apartment_id │
│    apartment_no │       │    name         │
│    status       │       │    surname      │
│    created_at   │       │    phone        │
└────────┬────────┘       │    email        │
         │ 1:N            │    type         │
         │                │    created_at   │
         ▼                └────────┬────────┘
┌─────────────────┐               │ 1:N
│      dues       │               │
├─────────────────┤               ▼
│ PK dues_id      │       ┌─────────────────┐
│ FK apartment_id │       │   complaints    │
│    year         │       ├─────────────────┤
│    month        │       │ PK complaint_id │
│    amount       │       │ FK resident_id  │
│    status       │       │    title        │
│    created_at   │       │    description  │
└────────┬────────┘       │    status       │
         │ 1:N            │    complaint_dt │
         │                │    created_at   │
         ▼                └─────────────────┘
┌─────────────────┐
│    payments     │       ┌─────────────────┐
├─────────────────┤       │     users       │
│ PK payment_id   │       ├─────────────────┤
│ FK dues_id      │       │ PK user_id      │
│    payment_date │       │    username     │
│    paid_amount  │       │    password     │
│    pay_method   │       │    role         │
│    status       │       │ FK resident_id  │
│    is_simulation│       │    created_at   │
│    card_holder  │       └─────────────────┘
│    card_mask    │
│    created_at   │
└─────────────────┘
```

### 5.1 İlişki Türleri

| Tablo 1 | Tablo 2 | İlişki | Açıklama |
|---------|---------|--------|----------|
| blocks | apartments | 1:N | Bir blokta birden fazla daire olabilir |
| apartments | residents | 1:N | Bir dairede birden fazla sakin olabilir |
| apartments | dues | 1:N | Bir daireye birden fazla aidat kaydedilebilir |
| dues | payments | 1:N | Bir aidat için birden fazla ödeme olabilir |
| residents | complaints | 1:N | Bir sakin birden fazla şikayet oluşturabilir |
| residents | users | 1:1 | Bir sakinin bir kullanıcı hesabı olabilir |

### 5.2 Normalizasyon Analizi (5NF)

Projedeki tüm tablolar **5. Normal Forma (5NF)** uygun şekilde tasarlanmıştır:

**1NF (Birinci Normal Form):**
- Tüm sütunlar atomik değer içerir. Örneğin `residents` tablosunda `name` ve `surname` ayrı sütunlardır.
- Her satır benzersizdir (PRIMARY KEY ile sağlanır).

**2NF (İkinci Normal Form):**
- Tüm non-key sütunlar birincil anahtara tam bağımlıdır.
- `dues` tablosunda `amount` ve `status`, `dues_id`'ye bağlıdır; `apartment_id`'ye değil.

**3NF (Üçüncü Normal Form):**
- Geçişli bağımlılık yoktur. Örneğin `payments` tablosunda blok adı tutulmaz; `dues_id → apartment_id → block_id` zinciri view ile çözülmüştür.
- Sakin adı `dues` tablosunda tekrarlanmaz, `residents` tablosundan JOIN ile alınır.

**BCNF (Boyce-Codd Normal Form):**
- `apartments` tablosunda `(block_id, apartment_no)` UNIQUE constraint ile her determinant candidate key'dir.

**4NF (Dördüncü Normal Form):**
- Çok değerli bağımlılık yoktur. Sakin-daire ve sakin-şikayet ilişkileri ayrı tablolarda tutulur.

**5NF (Beşinci Normal Form):**
- Tüm join bağımlılıkları birincil anahtardan türetilir. Hiçbir tabloda gereksiz veri tekrarı yoktur.

---

## 6. Veritabanı Yapıları Detayı

### 6.1 Constraints (Kısıtlayıcılar)

| Constraint Türü | Kullanım Yeri | Açıklama |
|-----------------|---------------|----------|
| **PRIMARY KEY** | Tüm tablolar | Birincil anahtar |
| **FOREIGN KEY** | apartments, residents, dues, payments, complaints, users | İlişkisel bütünlük |
| **UNIQUE** | blocks.block_name, users.username | Benzersiz değer zorunluluğu |
| **CHECK** | apartments.floor_no, dues.year, dues.amount, expenses.amount, residents.name | Veri doğrulama |
| **DEFAULT** | Tüm ENUM alanları, created_at | Varsayılan değer |

### 6.2 Index Yapıları

| Index Adı | Tablo | Alan(lar) | Amaç |
|-----------|-------|-----------|------|
| idx_apartments_block_id | apartments | block_id | Blok bazlı daire sorguları |
| idx_residents_apartment_id | residents | apartment_id | Daire-sakin ilişkisi |
| idx_dues_apartment_id | dues | apartment_id | Aidat listeleme |
| idx_dues_status | dues | status | Ödenmemiş aidat filtreleme |
| idx_dues_apartment_status | dues | apartment_id, status | Composite index |
| idx_payments_dues_id | payments | dues_id | Ödeme-aidat ilişkisi |
| idx_payments_status | payments | status | Bekleyen ödeme filtreleme |
| idx_complaints_resident_id | complaints | resident_id | Sakin şikayetleri |
| idx_complaints_status | complaints | status | Durum bazlı filtreleme |
| idx_expenses_date | expenses | expense_date | Tarih bazlı gider raporları |
| idx_users_resident_id | users | resident_id | Kullanıcı-sakin ilişkisi |

### 6.3 View'lar

| View Adı | Amaç |
|----------|------|
| **v_apartment_summary** | Her dairenin sakin, aidat ve ödeme özeti |
| **v_payment_details** | Ödemelerin detaylı görünümü (blok, daire, sakin bilgileriyle) |
| **v_complaint_details** | Şikayetlerin detaylı görünümü |
| **v_financial_summary** | Aylık gelir-gider finansal özeti |

### 6.4 Trigger'lar

| Trigger Adı | Tetikleyici | Amaç |
|-------------|-------------|------|
| **trg_after_payment_insert** | AFTER INSERT ON payments | Ödeme onaylandığında aidat durumunu otomatik güncelle |
| **trg_after_payment_update** | AFTER UPDATE ON payments | Ödeme durumu değiştiğinde aidat durumunu güncelle |
| **trg_after_resident_insert** | AFTER INSERT ON residents | Sakin eklendiğinde daire durumunu "dolu" yap |
| **trg_after_resident_delete** | AFTER DELETE ON residents | Son sakin silindiğinde daire durumunu "boş" yap |

### 6.5 Stored Procedure'lar

| Procedure Adı | Amaç |
|---------------|------|
| **sp_get_unpaid_dues** | Ödenmemiş aidatları blok ve yıl filtresiyle listele |
| **sp_get_monthly_income** | Belirli bir dönemdeki toplam geliri hesapla |
| **sp_add_expense** | Transaction ile güvenli gider ekleme |
| **sp_update_complaint_status** | Şikayet durumunu güncelle (validasyonlu) |

---

## 7. Ekran Görüntüleri ve Arayüz

> Aşağıdaki ekran görüntüleri projenin çalışır haldeki arayüzünü göstermektedir.

### 7.1 Admin Paneli - Dashboard

![Admin Dashboard](assets/screenshots/dashboard.png)

**Dashboard özellikleri:**
- Toplam blok, daire, sakin, aidat, ödeme istatistik kartları
- Son 5 onaylı ödeme listesi (blok/daire/tutar/yöntem)
- Mart 2026 aylık geliri (Stored Procedure ile hesaplanır)
- Hızlı erişim menüsü (7 modül)

### 7.2 Admin Paneli - Modüller

| Modül | Açıklama |
|-------|----------|
| Bloklar | Blok listeleme ve ekleme |
| Daireler | Daire listesi, kat ve doluluk durumu |
| Sakinler | Sakin kayıtları, arama filtresi |
| Aidatlar | Borç takibi, ödeme durumu badge'leri |
| Ödemeler | Tahsilat kayıtları, ödeme yöntemi filtreleme |
| Bekleyen Ödemeler | Kart simülasyonu onay/red ekranı |
| Şikayetler | Açık/çözüldü durum yönetimi (SP ile) |
| Giderler | Tür bazlı gider raporlama |

### 7.3 Sakin Paneli

| Sayfa | Açıklama |
|-------|----------|
| Ana Sayfa | Aidat özeti, son ödemeler, son şikayetler |
| Aidatlarım | Daire bazlı aidat listesi ve borç durumu |
| Aidat Öde | Kart simülasyonu — tutar otomatik belirlenir, değiştirilemez |
| Şikayetlerim | Şikayet ekleme ve durum takibi |
| Site Giderleri | Salt okunur gider listesi |

### 7.4 Tasarım Özellikleri

- Modern gradient arka plan (koyu lacivert → mavi)
- Cam efektli sidebar (glassmorphism)
- Responsive grid yapısı (CSS Grid + Flexbox)
- Mobil uyumlu tasarım (@media queries: 992px, 768px)
- Hover animasyonları ve geçiş efektleri
- Renk kodlu badge'ler: yeşil (ödendi/çözüldü), kırmızı (ödenmedi/açık)

---

## 8. Yapılan Araştırmalar

### 8.1 Veritabanı Tasarımı
- **Kaynak:** MySQL 8.0 Reference Manual, W3Schools SQL Tutorial
- **Konular:** Normalizasyon kuralları, INDEX optimizasyonu, VIEW kullanım senaryoları
- **Çözüm:** Tüm tablolar 5NF'a uygun tasarlandı, performans için composite index'ler eklendi.

### 8.2 PDO ve Güvenlik
- **Kaynak:** PHP.net PDO Manual, OWASP SQL Injection Prevention
- **Konular:** Prepared statements, XSS koruması, password hashing
- **Çözüm:** Tüm veritabanı işlemleri PDO prepared statements ile yapıldı.

### 8.3 Kart Simülasyonu
- **Kaynak:** PCI DSS özet dokümanları
- **Konular:** Kredi kartı veri güvenliği, maskeleme teknikleri
- **Çözüm:** Kart numarası sadece son 4 hanesi saklanır (masking), gerçek ödeme altyapısı kullanılmaz.

### 8.4 Responsive Tasarım
- **Kaynak:** CSS-Tricks, MDN Web Docs
- **Konular:** CSS Grid, Flexbox, CSS Variables
- **Çözüm:** Modern CSS Grid ve Flexbox yapıları kullanıldı, 992px ve 768px kırılma noktaları tanımlandı.

---

## 9. Genel Yapı

### 9.1 Proje Kapsamı

| Modül | İşlevler |
|-------|----------|
| **Blok Yönetimi** | Blok ekleme, listeleme |
| **Daire Yönetimi** | Daire ekleme, blok ilişkisi, doluluk durumu |
| **Sakin Yönetimi** | Sakin ekleme, daire atama, iletişim bilgileri |
| **Aidat Yönetimi** | Aidat ekleme, borç takibi, ödeme durumu |
| **Ödeme Yönetimi** | Nakit/kart/havale ödeme kaydı, simülasyon |
| **Şikayet Yönetimi** | Şikayet ekleme, durum güncelleme, filtreleme |
| **Gider Yönetimi** | Gider ekleme, tür bazlı raporlama, filtreleme |
| **Kullanıcı Yönetimi** | Admin/sakin rolü, şifreli giriş, yetkilendirme |

### 9.2 Gelecek Geliştirmeler

- [ ] E-posta bildirim sistemi ( aidat hatırlatmaları )
- [ ] PDF raporlama ( aidat dökümleri )
- [ ] Çoklu dil desteği ( TR / EN )
- [ ] Mobil uygulama ( React Native )
- [ ] Otomatik aidat oluşturma ( her ay otomatik )
- [ ] Anket/modül ekleme

---

## 10. Referanslar

1. **MySQL 8.0 Reference Manual** - Oracle Corporation, 2024.  
   https://dev.mysql.com/doc/refman/8.0/en/

2. **PHP: The Right Way** - Josh Lockhart, 2024.  
   https://phptherightway.com/

3. **OWASP SQL Injection Prevention Cheat Sheet** - OWASP Foundation, 2024.  
   https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html

4. **W3Schools SQL Tutorial** - Refsnes Data, 2024.  
   https://www.w3schools.com/sql/

5. **CSS-Tricks: A Complete Guide to Grid** - Chris Coyier, 2024.  
   https://css-tricks.com/snippets/css/complete-guide-grid/

6. **MDN Web Docs: PDO** - Mozilla Foundation, 2024.  
   https://developer.mozilla.org/en-US/docs/Glossary/PDO

7. **Font Awesome Icons** - Fonticons, Inc., 2024.  
   https://fontawesome.com/

---

## 11. Proje Bilgileri

| Bilgi | Değer |
|-------|-------|
| **Üniversite** | Kocaeli Üniversitesi |
| **Bölüm** | Bilişim Sistemleri Mühendisliği |
| **Ders** | TBL331: Veritabanı Yönetim Sistemleri |
| **Dönem** | 2025-2026 Bahar |
| **Proje Türü** | Dönem Projesi |
| **Geliştirme Tarihi** | Mayıs 2026 |

---

**Not:** Bu proje Kocaeli Üniversitesi Veritabanı Yönetim Sistemleri dersi kapsamında eğitim amaçlı geliştirilmiştir. Ticari kullanım için ek geliştirmeler gereklidir.
