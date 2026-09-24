# MyLibrary

Online bibliotēka, kas apvieno akadēmisko un izklaides literatūru vienā platformā. Lietotāji var pārlūkot grāmatas, lasīt aprakstus un atsauksmes, veidot personīgo bibliotēku, lejupielādēt PDF failus, rakstīt atsauksmes un saņemt personalizētus ieteikumus.

---

## Saturs

- [Par projektu](#par-projektu)
- [Funkcionalitāte](#funkcionalitāte)
- [Tehnoloģijas](#tehnoloģijas)
- [Projekta struktūra](#projekta-struktūra)
- [Datubāzes struktūra](#datubāzes-struktūra)
- [API maršruti](#api-maršruti)
- [Papildu funkcijas](#papildu-funkcijas)
- [Docker un izvietošana](#docker-un-izvietošana)

---

## Par projektu

MyLibrary ir tīmekļa lietotne, kas apvieno divas galvenās grāmatu kategorijas:

- Akadēmiskās grāmatas
- Grāmatas atpūtai (zinātniskā fantastika, klasika, detektīvi un citi žanri)

Projekts ir izstrādāts, lai lietotājiem nodrošinātu vienotu vidi gan mācību materiālu meklēšanai, gan literatūras lasīšanai brīvajā laikā, kā arī iespēju lejupielādēt grāmatas PDF formātā bezsaistes lasīšanai.

---

## Funkcionalitāte

### Nereģistrēta lietotāja iespējas

- Pārlūkot visas grāmatas
- Meklēt grāmatas pēc nosaukuma, autora vai ISBN
- Filtrēt grāmatas pēc nodaļām un žanriem
- Lasīt grāmatu īso aprakstu
- Lasīt citu lietotāju atsauksmes

### Reģistrēta lietotāja iespējas

- Viss, kas pieejams nereģistrētam lietotājam
- Pievienot grāmatas personīgajai bibliotēkai
- Mainīt lasīšanas statusu (lasu, izlasīju, vēl nelasīju)
- Lejupielādēt grāmatas PDF formātā
- Rakstīt, rediģēt un dzēst atsauksmes
- Atbildēt uz citu lietotāju komentāriem (viegla čata formā)
- Rediģēt savu profilu (vārds, pilsēta, dzimšanas datums, biogrāfija)
- Augšupielādēt un mainīt profila attēlu
- Mainīt paroli
- Dzēst savu kontu
- Saņemt personalizētus grāmatu ieteikumus

### Administratora iespējas

- Rediģēt savu profilu (vārds, pilsēta, dzimšanas datums, biogrāfija)
- Augšupielādēt un mainīt profila attēlu
- Mainīt paroli
- Dzēst savu kontu
- Pievienot, rediģēt un dzēst grāmatas
- Pievienot, rediģēt un dzēst žanrus
- Pārvaldīt lietotājus (bloķēt / aktivizēt)
- Apskatīt detalizētu statistiku par grāmatām un lietotājiem
- Redzēt skatījumu un lejupielāžu skaitu katrai grāmatai

---

## Tehnoloģijas

### Backend

- PHP 8.3
- Laravel 12
- MySQL
- Eloquent ORM
- REST API
- Token bāzēta autentifikācija

### Frontend

- Vue 3
- Vuetify 3
- Vite
- Vue Router
- JavaScript 
- Fetch API
- CSS3 (adaptīvs dizains)

### Papildu rīki

- Cloudflare R2 (failu glabāšana)
- Docker
- Railway (izvietošana)
- Git un GitHub


---

## Datubāzes struktūra

### Galvenās tabulas

**Lietotajs** - lietotāji
- kodsID (primārā atslēga)
- lietotaja_vards
- epasts
- parole (hash)
- loma (admins, registretajsklients, viesis)
- status (aktivs, blokets)
- registresanas_datums
- foto
- bio
- pilseta
- dzim_datums

**Gramata** - grāmatas
- ISBN (primārā atslēga)
- nosaukums
- autors
- gads
- lapu_skaits
- apraksts
- faila_pdf
- vaku_attels
- Zanra_ID (ārējā atslēga)
- Nodala_ID (ārējā atslēga)

**Zanrs** - žanri
- Zanra_ID (primārā atslēga)
- nosaukums
- gramatu_skaits
- Nodala

**Nodala** - nodaļas
- Nodala_ID (primārā atslēga)
- tips (akademiska, izglitojosa)

**LietotajGramatas** - lietotāja bibliotēka
- LietotajGramatas_ID (primārā atslēga)
- Lietotajs (ārējā atslēga)
- Gramatas (ārējā atslēga)
- statuss (lasu, izlasiju, vel nelasiju)
- pievienosanas_datums

**Atsauksmes** - atsauksmes un komentāri
- Atsauksmes_ID (primārā atslēga)
- Lietotaja_ID (ārējā atslēga)
- Gramatas_ID (ārējā atslēga)
- vertejums (1-5)
- komentārs
- vecakais_komentars (pašatsauce uz Atsauksmes_ID)

**Parskata** - skatījumi
- Parskata_ID
- parskatas_skaits
- Gramatas
- Lietotajs

**Lejupielade** - lejupielādes
- Lejupielade_ID
- Datums
- Gramatas_ID
- Lietotaja_ID

---

## API maršruti

### Autentifikācija

| Metode | Maršruts | Apraksts |
|--------|----------|----------|
| POST | /api/register | Reģistrācija |
| POST | /api/pieslēgties | Pieslēgšanās |
| GET | /api/check-auth | Autentifikācijas pārbaude |
| POST | /api/izrakstīties | Izrakstīšanās |
| POST | /api/check-user | Lietotāja esamības pārbaude |

### Grāmatas

| Metode | Maršruts | Apraksts |
|--------|----------|----------|
| GET | /api/books | Visas grāmatas |
| GET | /api/books/{isbn} | Konkrēta grāmata |
| GET | /api/books/search/{query} | Meklēšana |
| GET | /api/homepage-books | Grāmatas galvenajai lapai |
| GET | /api/genres | Visi žanri |
| GET | /api/nodalas | Visas nodaļas |
| GET | /api/recommendations | Personalizēti ieteikumi |

### Lietotāja bibliotēka

| Metode | Maršruts | Apraksts |
|--------|----------|----------|
| GET | /api/user/books | Lietotāja grāmatas |
| POST | /api/user/books/add | Pievienot grāmatu |
| PUT | /api/user/book/status | Mainīt statusu |
| DELETE | /api/user/book/{id} | Dzēst grāmatu |

### Atsauksmes

| Metode | Maršruts | Apraksts |
|--------|----------|----------|
| POST | /api/reviews | Pievienot atsauksmi vai atbildi |
| GET | /api/reviews/check/{bookId} | Pārbaudīt atsauksmi |
| GET | /api/books/{isbn}/reviews | Grāmatas atsauksmes |

### Profils

| Metode | Maršruts | Apraksts |
|--------|----------|----------|
| GET | /api/profile | Iegūt profilu |
| PUT | /api/profile | Atjaunināt profilu |
| POST | /api/profile/avatar | Augšupielādēt avatāru |
| POST | /api/profile/change-password | Mainīt paroli |
| DELETE | /api/profile | Dzēst kontu |

### Administratora maršruti

| Metode | Maršruts | Apraksts |
|--------|----------|----------|
| GET | /api/admin/users | Lietotāju saraksts |
| PUT | /api/admin/users/{id}/status | Mainīt lietotāja statusu |
| POST | /api/admin/books | Pievienot grāmatu |
| PUT | /api/admin/books/{isbn} | Rediģēt grāmatu |
| DELETE | /api/admin/books/{isbn} | Dzēst grāmatu |
| POST | /api/admin/genres | Pievienot žanru |
| PUT | /api/admin/genres/{id} | Rediģēt žanru |
| DELETE | /api/admin/genres/{id} | Dzēst žanru |
| GET | /api/admin/stats/books/{isbn} | Grāmatas statistika |
| GET | /api/admin/stats/users | Lietotāju statistika |
| GET | /api/admin/stats | Vispārējā statistika |


---

## Papildu funkcijas

### Personalizēti ieteikumi

Sistēma analizē lietotāja bibliotēku un iesaka grāmatas no tām pašām nodaļām, kuras lietotājs lasa visvairāk. Grāmatas, kas jau ir lietotāja bibliotēkā, netiek ieteiktas.

Ja lietotājs nav pieteicies vai viņa bibliotēka ir tukša, tiek rādītas populārākās grāmatas.

### Atsauksmes ar atbildēm

Atsauksmju sistēma atbalsta bezgalīgu atbilžu iegulstību. Katrs lietotājs var atbildēt uz jebkuru komentāru, un atbildes tiek attēlotas kā viegls čats ar atkāpēm.

### Failu glabāšana

PDF faili un attēli tiek glabāti Cloudflare R2 krātuvē, nodrošinot ātru piekļuvi un mērogojamību.

---

## Docker un izvietošana

Projektu var izvietot, izmantojot Docker konteineri.

### Dockerfile piemērs

```dockerfile
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    nodejs npm zip unzip git curl \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY biblioteka/ /var/www/html/

WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN npm install && npm run build

RUN php artisan storage:link

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000






