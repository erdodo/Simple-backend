api
Merhaba kod yazmadan backend yazabilme programı Simple’a hoşgeldiniz. Tablo oluşturma, veri düzenleme, yetkilendirme, özel eposta ayarları ve çok daha fazlası. Her şey ister API ile isterseniz basit arayüz ile gerçekleştirebilirsiniz.

v1
v1 standartı altında yapılan tüm işlemler yetkilendirmeye tabidir. Örnek verecek olursak; kullanıcılar tablosundan epostası: robot@erdoganyesil.com.tr olan kullanıcıyı düzenlemek istiyorsunuz. Öncelikle token sorgusu atılır, eğer token var ise bu tokenin sahibi bulunmaya çalışılır, kullanıcı bulunduktan sonra yetki grubuna bakılır. Elimizdeki 3 veri ile yetki sorgusu atılır.

İstenilen method(update)
İstenilen tablo(users)
İstenilen yetki grubu(1)
Bu filtre bilgilerine uygun yetki var ise işleme devam edebilirsiniz.

v1 standartı altında tüm veritabanı işlemleri bulunmaktadır. Bu standart dışındaki tüm apiler işleri kolaylaştırmak, hızlandırmak ve daha güvenli hale getirmek için yazılmıştır.

POST
System Control
{{base_url}}/api/
Kullanıcının token’i hala geçerli mi? Kontrolü yapmak için kullanılır. Yüksüz bir API’dir. Kullanıcı başı istek sınırlandırmasına takılmaz.

Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
GET
List (Params)
{{base_url}}/api/v1/{{lang}}/test/list?limit=17
Tablonuzdan verileri liste halinde çekmek için bu api kullanılır. Olabildiğince fazla filtreleme seçeneği bulunmaktadır.

Filtre örnekleri; GET, POST, FormData gibi Requestlere özel olarak dökümanlara ayrılmıştır.

Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
Query Params
filters
["name=id"]
default = []

page
1
default = 1

sorts
["id=false"]
default = []

like
["name=auths"]
limit
17
Example
Request
Dart - http
var request = http.Request('POST', Uri.parse('{{base_url}}/api/v1/{{lang}}/users/list?filters=["name=id"]&page=1&limit=10&sorts=["id=false"]&like=["name=auths"]'));


http.StreamedResponse response = await request.send();

if (response.statusCode == 200) {
  print(await response.stream.bytesToString());
}
else {
  print(response.reasonPhrase);
}
Response
json
{
    "records": [],
    "fields": {
        "id": {
            "id": "1",
            "name": "id",
            "display": "ID",
            "type": "number",
            "enums": null,
            "required": "0",
            "min_length": "1",
            "max_length": "11",
            "min_value": null,
            "max_value": null,
            "relation_table": null,
            "relation_id": null,
            "relation_columns": null,
            "mask": null,
            "regex": null,
            "lang_support": "0",
            "state": "1",
            "description": "",
            "created_at": "2023-01-04 15:55:15",
            "updated_at": "2023-01-04 22:02:08",
            "own_id": "1",
            "user_id": "1"
        },
        "name": {
            "id": "2",
            "name": "name",
            "display": "Name",
            "type": "sort_text",
            "enums": null,
            "required": "0",
            "min_length": null,
            "max_length": null,
            "min_value": null,
            "max_value": null,
            "relation_table": null,
            "relation_id": null,
            "relation_columns": null,
            "mask": null,
            "regex": null,
            "lang_support": "0",
            "state": "1",
            "description": "",
            "created_at": "2023-01-04 15:56:35",
            "updated_at": "2023-01-04 22:12:27",
            "own_id": "1",
            "user_id": "1"
        },
        "surname": {
            "id": "24",
            "name": "surname",
            "display": "Soyad",
            "type": "sort_text",
            "enums": null,
            "required": "0",
            "min_length": null,
            "max_length": null,
            "min_value": null,
            "max_value": null,
            "relation_table": null,
            "relation_id": null,
            "relation_columns": null,
            "mask": null,
            "regex": null,
            "lang_support": "0",
            "state": "1",
            "description": "",
            "created_at": "2023-01-04 16:13:26",
            "updated_at": "2023-01-06 22:04:37",
            "own_id": "1",
            "user_id": "1"
        },
        "email": {
            "id": "25",
            "name": "email",
            "display": "E-posta",
            "type": "email",
            "enums": null,
            "required": "1",
            "min_length": null,
            "max_length": null,
            "min_value": null,
            "max_value": null,
            "relation_table": null,
            "relation_id": null,
            "relation_columns": null,
            "mask": null,
            "regex": null,
            "lang_support": "0",
            "state": "1",
            "description": "",
            "created_at": "2023-01-04 16:13:26",
            "updated_at": "2023-01-06 22:59:46",
            "own_id": "1",
            "user_id": "1"
        },
        "password": {
            "id": "27",
            "name": "password",
            "display": "Şifre",
            "type": "pass",
            "enums": null,
            "required": "1",
            "min_length": null,
            "max_length": null,
            "min_value": null,
            "max_value": null,
            "relation_table": null,
            "relation_id": null,
            "relation_columns": null,
            "mask": null,
            "regex": null,
            "lang_support": "0",
            "state": "1",
            "description": "",
            "created_at": "2023-01-04 16:13:58",
            "updated_at": "2023-01-06 22:59:39",
            "own_id": "1",
            "user_id": "1"
        },
        "phone": {
            "id": "26",
            "name": "phone",
            "display": "Telefon",
            "type": "phone",
            "enums": null,
            "required": "0",
            "min_length": null,
            "max_length": null,
            "min_value": null,
            "max_value": null,
            "relation_table": null,
            "relation_id": null,
            "relation_columns": null,
            "mask": null,
            "regex": null,
            "lang_support": "0",
            "state": "1",
            "description": "",
            "created_at": "2023-01-04 16:13:58",
            "updated_at": "2023-01-04 22:11:26",
            "own_id": "1",
            "user_id": "1"
        },
        "settings": {
            "id": "28",
            "name": "settings",
            "display": "Ayarlar",
            "type": "json",
            "enums": null,
            "required": "0",
            "min_length": null,
            "max_length": null,
            "min_value": null,
            "max_value": null,
            "relation_table": null,
            "relation_id": null,
            "relation_columns": null,
            "mask": null,
            "regex": null,
            "lang_support": "0",
            "state": "1",
            "description": "",
            "cre
POST
List Data
{{base_url}}/api/v1/{{lang}}/lists/list
Add request description…
POST
Show Data
{{base_url}}/api/v1/{{lang}}/users/show/id:1
Tablonuzdan tek veri çekmek için kullanabileceğiniz bu API içinde ilgili data ve kolonlarını barındırmaktadır.

Verinizi 2 farklı şekilde filtreleyebilirsiniz:

İd kullanımı
Eğer ki show/ ‘dan sonra bir numara gönderirseniz bu sistem tarafından id olarak tanımlanır ve ona uygun response döner.

2. Filtre kullanımı

Filtre kullanımından kasıt istenilen veride unique bir kolonun key:value şeklinde request atılmadır. Örnek verecek olursak ; show/email:robot@erdoganyesil.com.tr . Kullanıcılar tablosunda eposta unique bir değişken olduğu için sadece tek bir veri bulunabilir ve response olarak döner.

örn:

name:Test7
id:1
phone:05555555555
Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
Query Params
name
TEST6
surname
TEST7SUR
Bodyraw (json)
json
{
  "name": "test6"
}
GET
Create Columns Data
{{base_url}}/api/v1/{{lang}}/lists/create
Klasik düzenleme yapılacağı zaman id kullanılıyor,fakat farklı bir ikincil anahtar kullanılacaksa "column:deger" şeklinde kullanılabilir

örn:

name:Test7
id:1
Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
Query Params
name
TEST6
surname
TEST7SUR
Bodyraw (json)
json
{
  "name": "test6"
}
POST
Add data
{{base_url}}/api/v1/{{lang}}/test/add
Create request’inde aldığımız kolonları add API’sine göndererek veritabanına ekleme işlemi yapabiliriz. Eğer ki ekleme yaparken yetkisi olmayan kolonlara ekleme yapılmaya çalışırsa o kolonlar silinir ve eklenmez.

Image eklemesi yapılırken dosya boyutlandırması yapılır ve 4 farklı kolon JSON olarak kaydı gerçekleşir. Bu kolonlar full ve mini olarak iki ayrı boyutlandırma ve bunlara ek başına sisteminizin çalıştığı domain adresi eklenmiş hali olan link kolonlarıdır.

File eklemesi yaparken image ile aynı mantıkta çalışır sadece mini kolonu boş gelir.

Tablo eklemesi yaparken(lists) fields tablosunda var olan kolonları eklemelisiniz. Kolonların yetersiz kaldığı durumlarda öncelikle kolonları eklemeli ve tablo eklemeye geri dönmelisiniz. Orada seçtiğiniz durumlara özel sql kodu üretilmekte ve çalıştırılmaktadır. Eklediğiniz tablo sistem tarafından ana yöneticiye tam yetki verir. Siz ilgili yetki gruplarınıza özel eklebilirsiniz.

Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
Query Params
email
asdf
password
123123
surname
Test 9 sur
name
Test 9
Bodyform-data
name
image_test
file
/C:/Users/erdo_/OneDrive/Masaüstü/indir.jpg
asdfasdf
asdfasd
GET
Edit Columns Data
{{base_url}}/api/v1/{{lang}}/users/edit/id:1
Klasik düzenleme yapılacağı zaman id kullanılıyor,fakat farklı bir ikincil anahtar kullanılacaksa "column:deger" şeklinde kullanılabilir

örn:

name:Test7
id:1
Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
Query Params
name
TEST6
surname
TEST7SUR
Bodyraw (json)
json
{
  "name": "test6"
}
POST
Update Data
{{base_url}}/api/v1/{{lang}}/test/update/1
Klasik düzenleme yapılacağı zaman id kullanılıyor,fakat farklı bir ikincil anahtar kullanılacaksa "column:deger" şeklinde kullanılabilir

örn:

name:Test7
id:1
Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
Query Params
name
TEST6
surname
TEST7SUR
Bodyraw (json)
json
{
  "name": "test2"
}
DEL
Delete Data
{{base_url}}/api/v1/{{lang}}/test/delete/id:3
Klasik düzenleme yapılacağı zaman id kullanılıyor,fakat farklı bir ikincil anahtar kullanılacaksa "column:deger" şeklinde kullanılabilir

örn:

name:Test7
id:1
Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
Query Params
name
TEST6
surname
TEST7SUR
Bodyraw (json)
json
{
  "name": "test6"
}
account
Add folder description…
Forgot password
Columns apisinden kolon bilgileri ve captcha gelmektedir.

Send email apisinde captcha ve eposta bilgisi gönderilir.

Kullanıcıya eposta yoluyla bir otp kodu gönderilir.

New password apisinde OTP, kullanıcının epostası, yeni şifresi ve yeni şifre doğrulaması istenir.

kontroller backendde yapılır ve mesaj döndürülür.

GET
Columns
{{base_url}}/api/account/forgot_password
Üyelik için zorunlu alanlar ve captcha kodu dönmektedir.

Captcha kodu base64 olarak döner.

POST
Send Email
{{base_url}}/api/account/forgot_password
Üyelik için zorunlu alanlar ve captcha kodu dönmektedir.

Captcha kodu base64 olarak döner.

Query Params
email
erdoganyesil3@gmail.com
otp
85863
Bodyraw (json)
json
{
  "email": "robot2@erdoganyesil.com.tr",
  "captcha": "08354"
}
POST
New password
{{base_url}}/api/account/forgot_new_password?otp=805815&email=robot2@erdoganyesil.com.tr&password=456&password_verification=456
Üyelik için zorunlu alanlar ve captcha kodu dönmektedir.

Captcha kodu base64 olarak döner.

Query Params
otp
805815
email
robot2@erdoganyesil.com.tr
password
456
password_verification
456
Change email
Columns apisinde gerekli kolonlar döndürülür.

Send email apisinde kullanıcının yeni epostasına OTP maili gider.

New email apisinde yeni email ve OTP gönderilir ve değişmiş olur

GET
Columns
{{base_url}}/api/account/change_email
Üyelik için zorunlu alanlar ve captcha kodu dönmektedir.

Captcha kodu base64 olarak döner.

Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
POST
Send Email
{{base_url}}/api/account/change_email?email=erdoganyesil3@gmail.com
Üyelik için zorunlu alanlar ve captcha kodu dönmektedir.

Captcha kodu base64 olarak döner.

Request Headers
Authorization
47bf57c664273bebe3ca1becb80c34b7
Query Params
email
erdoganyesil3@gmail.com
otp
85863
Bodyraw (json)
json
{
  "email": "robot2@erdoganyesil.com.tr",
  "captcha": "08354"
}
POST
New email
{{base_url}}/api/account/change_new_email
Üyelik için zorunlu alanlar ve captcha kodu dönmektedir.

Captcha kodu base64 olarak döner.

Query Params
otp
805815
email
robot2@erdoganyesil.com.tr
password
456
password_verification
456
POST
Login
{{base_url}}/api/account/login
GET isteği dışındaki tüm istekleri destekler.

Güvenlik önlemlerinden dolayı get isteği kapatılmıştır.

Query Params
email
asdf@asd.asd
password
asdf
Bodyraw (json)
json
{
  "email": "robot@erdoganyesil.com.tr",
  "password": "RobotKullanıcı"
}
Example
Login (Get methodu)
Request
Dart - http
var request = http.Request('GET', Uri.parse('{{base_url}}/api/account/login?email=asdf@asd.asd&password=asdf'));
 
 
http.StreamedResponse response = await request.send();
 
if (response.statusCode == 200) {
  print(await response.stream.bytesToString());
}
else {
  print(response.reasonPhrase);
}
View more
Response
json
 
POST
Register
{{base_url}}/api/account/register?name=Erdoğan&surname=Yeşil&email=erdoganyesil3@gmail.com
Add request description…
Query Params
name
Erdoğan
surname
Yeşil
email
erdoganyesil3@gmail.com
GET
Register Columns
{{base_url}}/api/account/register
Üyelik için zorunlu alanlar ve captcha kodu dönmektedir.

Captcha kodu base64 olarak döner.
