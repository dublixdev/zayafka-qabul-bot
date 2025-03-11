```markdown
# Telegram Bot 🤖

PHP, Nutgram va Laravel yordamida yaratilgan kuchli Telegram bot. Foydalanuvchilar bilan muloqot, xabarlarni tarqatish va kanalga qo'shilish so'rovlarini boshqarish imkoniyatlari.

## ✨ Imkoniyatlari

- **Xush kelibsiz xabarlari**: Yangi foydalanuvchilar uchun shaxsiylashtirilgan salomlash.
- **Admin paneli**: Foydalanuvchilarni boshqarish, xabarlarni tarqatish va so'rovlarni qabul qilish.
- **Reklama boshqaruvi**: Saqlangan reklamalarni osongina yuborish.
- **Statistika**: Real vaqtda foydalanuvchilar va so'rovlar sonini ko'rsatish.
- **Paketli jarayonlar**: Background job'lar yordamida samarali so'rov va xabar boshqaruvi.
- **Ko'p kanalni qo'llab-quvvatlash**: Maxsus kanallar uchun so'rovlarni tasdiqlash.

## 🚀 O'rnatish

1. **Repozitoriyani klonlash**
   ```bash
   git clone https://github.com/foydalanuvchi/telegram-bot.git
   cd telegram-bot
   ```

2. **Qaramliklarni o'rnatish**
   ```bash
   composer install
   ```

3. **Sozlamalar**
   - `.env.example` faylini `.env` nomi bilan nusxalang
   - Bot tokeni va ma'lumotlar bazasi parametrlarini kiriting:
     ```env
     TELEGRAM_TOKEN=sizning_bot_token
     DB_DATABASE=ma'lumotlar_bazasi_nomi
     DB_USERNAME=foydalanuvchi
     DB_PASSWORD=parol
     ```

4. **Migratsiyalarni ishga tushirish**
   ```bash
   php artisan migrate
   ```

5. **Navbatni ishga tushirish**
   ```bash
   php artisan queue:work
   ```

## 🔧 Sozlamalar

### Muhit o'zgaruvchilari
| Kalit             | Tavsif                          |
|-------------------|----------------------------------|
| `TELEGRAM_TOKEN`  | Telegram bot tokeni             |
| `DB_*`            | Ma'lumotlar bazasi parametrlari |

### Webhook sozlamasi
Botning webhook URL manzilini serveringizga yo'naltiring.

---

## 🎮 Foydalanish

### Admin buyruqlari 🛠️
| Buyruq                | Tavsif                                  |
|-----------------------|------------------------------------------|
| `/admin`              | Admin paneliga kirish                   |
| `/send`               | Xabar tarqatish (habarga reply qiling)   |
| `/saqla`              | Reklamani saqlash (habarga reply qiling) |
| `/remove`             | Saqlangan reklamani o'chirish           |
| `/stat`               | Jami foydalanuvchilar soni               |
| `/chat_join`          | So'rovlarni boshqarish                   |
| `/boshlash`           | Barcha so'rovlarni tasdiqlash            |
| `/boshlash_kanal -100xxx` | Maxsus kanal so'rovlarini tasdiqlash |

### Foydalanuvchi interfeysi 👥
- Foydalanuvchilar `/start` bosganda salom yoki saqlangan reklama oladi.
- Barcha yangi foydalanuvchilar avtomatik bazaga qo'shiladi.

---

## 📊 Batafsil imkoniyatlar

### Xabarlarni tarqatish
- Xabarlarni paketlar halida yuborish (1 sekundda 15 foydalanuvchi).
- `/stat_send` buyrug'i bilan progressni kuzatish.

### So'rovlarni boshqarish
- Navbatdagi so'rovlarni paketlar halida tasdiqlash (1 sekundda 20 so'rov).
- Global va maxsus kanal so'rovlarini qo'llab-quvvatlash.

### Reklama boshqaruvi
- `/saqla` buyrug'i bilan tugmachalarni saqlash.
- `/remove` bilan reklamalarni darhol o'chirish.

---

## 🤝 Hissa qo'shish

1. Repozitoriyani forking qiling.
2. Yangi branch yarating: `git checkout -b feat/yangi-imkoniyat`.
3. O'zgarishlarni commit qiling: `git commit -m 'Yangi imkoniyat qo'shildi'`.
4. Branchga push qiling: `git push origin feat/yangi-imkoniyat`.
5. Pull Request yuboring.

---

## 📄 Litsenziya

MIT litsenziyasi ostida tarqatilgan. Batafsil ma'lumot uchun `LICENSE` fayliga murojaat qiling.
```

Ushbu README.md fayli:
- Barcha texnik tushunchalar o'zbek tilida izohlangan
- Emojilar bilan vizual jozibadorlik
- Kod bloklari va jadvallar bilan tushunarli formatda
- Loyihani tezda tushunish va ishga tushirish uchun barcha kerakli qo'llanmalarni o'z ichiga oladi
