# AirTransfer Projesi — Geliştirme Süreci (Prompt) ve Araştırma Soruları



### 1. Soru: Oturum Güvenliğini Sağlama (Session Fixation Koruması)
**Soru:**
> "`login.php` dosyasını kodluyorum. Kullanıcı e-posta ve şifresini doğru girdiğinde oturumu başlatacağım. Ancak oturumun çalınmasını (Session Hijacking) veya saldırganın önceden belirlediği bir ID ile oturuma sızmasını (Session Fixation) engellemek için, session değişkenlerini atamadan hemen önce hangi PHP güvenlik fonksiyonunu çağırmalıyım ve mantığı nedir?"

**Cevap ve Çözüm:**
Kullanıcının yetki seviyesi değiştiğinde (ziyaretçiden giriş yapmış kullanıcıya geçtiğinde) tarayıcıdaki oturum kimliğini yenilemen gerekir. Bunun için `session_regenerate_id(true);` fonksiyonunu kullanmalısın. 

Bu fonksiyon, mevcut oturum verilerini koruyarak kullanıcıya tamamen yeni ve rastgele bir Session ID atar. Parantez içindeki `true` parametresi ise sunucudaki eski session dosyasını silerek saldırganın elindeki eski ID'yi tamamen geçersiz kılar.

---

### 2. Soru: Veritabanında Tablolar Arası İlişki ve Veri Silme Politikası
**Soru:**
> "`airbnb_transfer.sql` dosyasında veritabanı mimarisini tasarlıyorum. `users` tablosu ile `transfers` tablosunu `user_id` üzerinden birbirine bağlayacağım. Sistemde bir kullanıcı hesabını tamamen sildiğinde, o kullanıcıya ait eski transfer kayıtlarının veritabanında kalıp sistemi şişirmesini (öksüz kayıt olmasını) istemiyorum. MySQL'in bunu otomatik yapması için yabancı anahtar (Foreign Key) tanımına hangi kuralı eklemeliyim?"

**Cevap ve Çözüm:**
İlişkisel veritabanlarında veri bütünlüğünü (Referential Integrity) korumak için `ON DELETE CASCADE` kuralını kullanmalısın. 

`transfers` tablosunu oluştururken yabancı anahtar kısıtlamasının (Constraint) sonuna `ON DELETE CASCADE` eklediğinde; bir kullanıcı silindiği an, MySQL arka planda o kullanıcıya ait olan tüm transfer satırlarını otomatik olarak temizler. Böylece kod tarafında ekstradan bir silme sorgusu yazmana gerek kalmaz.

---

### 3. Soru: IDOR ve Yetkilendirme Zafiyetlerini Engelleme
**Soru:**
> "`edit_transfer.php` ve `delete_transfer.php` sayfalarını yazıyorum. Sistemde birden fazla kullanıcı olacak. Giriş yapmış kötü niyetli bir kullanıcının, tarayıcının adres çubuğundaki ID değerini elle değiştirerek (örneğin `id=5` yerine `id=6` yazarak) başka bir kullanıcının transfer verilerini görmesini veya silmesini nasıl engellerim? SQL sorgusunu nasıl kurgulamalıyım?"

**Cevap ve Çözüm:**
Bu durum web dünyasında **IDOR (Insecure Direct Object Reference)** olarak bilinir. Sadece `WHERE id = ?` kontrolü yapmak yetersizdir. Güvenlik için sorguya mutlak surette oturum açan kişinin ID'sini de bir parametre olarak eklemelisin.

Sorguyu `WHERE id = ? AND user_id = ?` şeklinde kurgulamalısın. Buradaki `user_id` değerini dışarıdan gelen veriden değil, doğrudan güvenli olan `$_SESSION['user_id']` değişkeninden almalısın. Böylece kullanıcı ID'yi değiştirse bile, o kayıt kendisine ait değilse sorgu hiçbir veri döndürmez ve yetkisiz işlem engellenmiş olur.

---

### 4. Soru: Dinamik Çıktılarda XSS (Zararlı Yazılım Enjeksiyonu) Koruması
**Soru:**
> "Kullanıcıların formlara girdikleri müşteri isimleri, notlar veya lokasyon gibi verileri `index.php` veya `edit_transfer.php` sayfalarında HTML içinde ekrana yazdıracağım. Kullanıcının bu alanlara yanlışlıkla veya kötü niyetle JavaScript kodu (`<script>...</script>`) yazması durumunda bu kodun tarayıcıda çalışıp sistemi ele geçirmesini (XSS açığı) nasıl önlerim? Her sayfada uzun uzun kod yazmamak için global bir fonksiyon üretebilir miyiz?"

**Cevap ve Çözüm:**
Ekrana basılan her dinamik verinin HTML etiketlerinden arındırılması veya dönüştürülmesi gerekir. Bunun için `config.php` gibi global bir dosyada `htmlspecialchars()` fonksiyonunu saran bir yardımcı fonksiyon tanımlayabilirsin.

```php
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```
---

### 5. Soru:Zamanı Geçmiş Operasyonları Yakalama (İş Mantığı Algoritması)
**Soru:**
> "index.php sayfasında transferleri listeliyorum. Şöyle bir iş mantığına (Business Logic) ihtiyacım var: Eğer bir transferin saati gelip geçtiyse ancak sistemde durumu hala 'bekliyor' (yani tamamlandı veya iptal yapılmamış) olarak kalmışsa, bu gözden kaçan bir operasyondur. Döngü içinde bu spesifik durumu PHP ile nasıl tespit edip arayüzde kullanıcıya görsel bir uyarı verebilirim?"

**Cevap ve Çözüm:**
Mevcut zaman (time()) ile veritabanından gelen transfer tarihini (transfer_datetime) karşılaştıran ve statüyü denetleyen mantıksal bir süzgeç kurmalısın.

Döngünün içerisine şu mantıksal koşulu ekleyebilirsin:

```php
$isPast = strtotime($t['transfer_datetime']) < time() && $t['status'] === 'bekliyor';
```
Burada veritabanındaki tarihi strtotime() ile saniyeye çevirip şu anki zamandan küçük mü diye bakıyoruz. Eğer küçükse ve statü hala `'bekliyor'` ise `$isPast` değişkeni `true` döner. Bu değişkene bakarak ilgili tablo satırına (`<tr>`) özel bir CSS sınıfı (`row-past`) atayıp rengini soldurabilir ve yanına "Geçmiş" rozeti ekleyebilirsin.

---

### 6. Soru: Dinamik Sorgu Oluşturma ve Güvenli Filtreleme
**Geliştirme Aşamasındaki Soru:**
> "`index.php` sayfasında transferleri listelerken hem kelime araması (Arama Kutusu) hem de durum filtresi (Bekliyor, Tamamlandı, İptal) eklemek istiyorum. Kullanıcın seçtiği kriterlere göre SQL sorgusundaki `WHERE` şartı dinamik olarak değişmeli. SQL Injection yemeden, PDO prepared statements ile bu dinamik sorgu yapısını ve parametre dizisini (`$params`) nasıl kurgulayabilirim?"

**Cevap ve Çözüm:**
Bu tür dinamik filtrelemelerde SQL cümlesini düz metin olarak birleştirmek yerine bir `$where` dizisi ve buna paralel bir `$params` dizisi oluşturmalısın.

```php
$where  = ['t.user_id = ?'];
$params = [(int)$_SESSION['user_id']];

if ($filterStatus !== 'all') {
    $where[]  = 't.status = ?';
    $params[] = $filterStatus;
}

if ($filterSearch !== '') {
    $where[]  = '(t.customer_name LIKE ? OR t.flight_no LIKE ?)';
    $like     = '%' . $filterSearch . '%';
    $params   = array_merge($params, [$like, $like]);
}

$sql = 'SELECT * FROM transfers t WHERE ' . implode(' AND ', $where);
```
Bu mantıkla, koşulları bir diziye toplar ve en son `implode(' AND ', $where)` ile birleştirirsin. Değerleri doğrudan SQL içine yazmayıp `?` koyduğun ve `$params` dizisiyle `execute($params)` metoduna gönderdiğin için sorgun tamamen güvenli olur.

---
### 7. Mükerrer Kayıtları Önleme (Post-Redirect-Get / PRG Deseni)

**Soru:**
`add_transfer.php` sayfasında formu doldurup "Kaydet" butonuna basıldığında ekleme işlemi başarıyla yapılıyor. Ancak kullanıcı başarı sayfasındayken tarayıcıyı yenilerse (F5) aynı transfer mükerrer kayıt olarak tekrar ekleniyor. Bu nasıl engellenir?

**Çözüm:**

Bu sorun web geliştirmede çok yaygındır; çözümü **PRG (Post-Redirect-Get)** tasarım desenidir.

Form POST isteği ile gönderilip veritabanı kaydı tamamlandıktan hemen sonra, doğrudan başarı mesajı render etmek yerine tarayıcıyı başka bir sayfaya (GET isteğine) yönlendirmelisin:

```php
header('Location: index.php');
exit;
```

Böylece kullanıcı sayfayı yenilediğinde POST isteğini değil, yönlendiği listenin GET isteğini yenilemiş olur ve mükerrer kayıt oluşması engellenir.

---

### 8. Finansal Veriler İçin Doğru Veritabanı Veri Tipi Seçimi

**Soru:**
`airbnb_transfer.sql` dosyasında transfer ücretlerini tutmak için `price` alanı açılacak. PHP ve MySQL projelerinde fiyat verilerini saklarken `FLOAT` veya `DOUBLE` yerine neden `DECIMAL` tipi öneriliyor?

**Çözüm:**

Finansal ve parasal değerler için **asla `FLOAT` veya `DOUBLE` kullanılmamalıdır.** Bu veri tipleri yaklaşık değerli (floating-point) sayılardır ve ikilik (binary) sistemde saklandıkları için küçük yuvarlama hatalarına neden olurlar:
( 0.1 + 0.2 = 0.30000000004)
**`DECIMAL(10,2)`** ise tam değerli (fixed-point) bir veri tipidir. Sayıyı tıpkı bir string gibi, hassasiyet kaybı yaşamadan saklar. Toplam gelir hesaplarken kuruşların doğru toplanması için `DECIMAL` kullanmak zorunludur.

---

### 9. Form Doğrulama Hatalarında Verileri Hatırlama (UX Geliştirme)

**Soru:**
`register.php` sayfasında şifreler eşleşmezse sayfa hata veriyor ve kullanıcının yazdığı Ad Soyad ile E-posta alanları siliniyor. Form alanlarının değerleri nasıl "akılda" tutulur?

**Çözüm:**

Formun başında boş bir `$old` dizisi tanımla ve POST edilen verileri bu diziye aktar:

```php
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']  = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    // ... doğrulama adımları ...
}
```

HTML tarafındaki input elemanlarına `value` özniteliği ekle:

```html
<input type="text"  name="name"  value="<?= e($old['name']) ?>">
<input type="email" name="email" value="<?= e($old['email']) ?>">
```

Böylece bir doğrulama hatası oluşsa bile kullanıcının daha önce yazdığı veriler input alanlarına otomatik olarak geri yazılır.

---

### 10. Tek Seferlik Bildirim Sistemi (Flash Messages)

**Soru:**
Bir transfer başarıyla eklendiğinde veya silindiğinde, yönlenilen sayfada ekranın üstünde yeşil bir alert kutusu göstermek istiyorum. Bu mesaj yalnızca bir kez görünmeli, sayfa yenilendiğinde kaybolmalı. PHP Session ile bu mekanizma nasıl kurulur?

**Çözüm:**

Bu yapıya **Flash Message (Anlık Mesaj)** sistemi denir.

**1. Mesajı session'a ekle ve yönlendir:**

```php
$_SESSION['flash'][] = ['type' => 'success', 'message' => 'Kayıt silindi.'];
header('Location: index.php');
exit;
```

**2. `header.php` şablonuna mesajları göster ve hemen sil:**

```php
<?php if (!empty($_SESSION['flash'])): ?>
    <?php foreach ($_SESSION['flash'] as $flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
```

Buradaki kritik nokta, mesajları HTML olarak ekrana bastıktan hemen sonra `unset($_SESSION['flash'])` ile silmektir. Böylece kullanıcı sayfayı F5 ile yenilediğinde dizi boş olacağı için uyarı kutusu bir daha görünmez.
