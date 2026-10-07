export const ERROR_MESSAGES = Object.freeze({
  addGenre: 'Žanru neizdevās pievienot. Mēģiniet vēlreiz.',
  accessDenied: 'Piekļuve liegta. Pārbaudiet sava konta tiesības.',
  accountBlocked: 'Jūsu konts ir bloķēts. Sazinieties ar administratoru.',
  addBook: 'Grāmatu neizdevās pievienot. Mēģiniet vēlreiz.',
  addReply: 'Atbildi neizdevās pievienot. Mēģiniet vēlreiz.',
  addReview: 'Atsauksmi neizdevās publicēt. Mēģiniet vēlreiz.',
  addToLibrary: 'Grāmatu neizdevās pievienot bibliotēkai. Mēģiniet vēlreiz.',
  changePassword: 'Paroli neizdevās nomainīt. Mēģiniet vēlreiz.',
  changeStatus: 'Grāmatas statusu neizdevās mainīt. Mēģiniet vēlreiz.',
  deleteAccount: 'Profilu neizdevās dzēst. Mēģiniet vēlreiz.',
  deleteBook: 'Grāmatu neizdevās dzēst. Mēģiniet vēlreiz.',
  deleteGenre: 'Žanru neizdevās dzēst. Mēģiniet vēlreiz.',
  duplicateBook: 'Šī grāmata jau ir jūsu bibliotēkā.',
  generic: 'Radās neparedzēta kļūda. Mēģiniet vēlreiz.',
  genreIdMissing: 'Žanra identifikators nav atrasts.',
  genreNameRequired: 'Lūdzu, ievadiet žanra nosaukumu.',
  genreNotFound: 'Žanrs nav atrasts.',
  invalidData: 'Ievadītie dati nav derīgi. Pārbaudiet tos un mēģiniet vēlreiz.',
  loadBook: 'Grāmatas informāciju neizdevās ielādēt. Mēģiniet vēlreiz.',
  loadBooks: 'Grāmatas neizdevās ielādēt. Mēģiniet vēlreiz.',
  loadGenres: 'Žanrus neizdevās ielādēt. Mēģiniet vēlreiz.',
  loadProfile: 'Profila datus neizdevās ielādēt. Mēģiniet vēlreiz.',
  login: 'Pieslēgšanās neizdevās. Pārbaudiet e-pastu un paroli.',
  network: 'Neizdevās izveidot savienojumu ar serveri. Pārbaudiet interneta savienojumu.',
  noPdf: 'Šai grāmatai PDF fails nav pieejams.',
  notJson: 'Servera atbilde nav derīgā formātā. Mēģiniet vēlreiz.',
  passwordRequired: 'Lūdzu, ievadiet paroli.',
  emailAlreadyRegistered: 'Lietotājs ar šo e-pasta adresi jau eksistē.',
  invalidEmail: 'Lūdzu, ievadiet derīgu e-pasta adresi.',
  invalidLogin: 'Nepareizs e-pasts vai parole.',
  missingFields: 'Lūdzu, aizpildiet visus laukus.',
  passwordTooShort: 'Parolei jābūt vismaz 6 rakstzīmes garai.',
  ratingRequired: 'Lūdzu, izvēlieties vērtējumu.',
  registrationFailed: 'Reģistrācija neizdevās. Pārbaudiet ievadītos datus un mēģiniet vēlreiz.',
  replyRequired: 'Lūdzu, uzrakstiet atbildi.',
  reviewExists: 'Jūs jau esat uzrakstījis atsauksmi par šo grāmatu.',
  serverDownload: 'Lejupielādi neizdevās reģistrēt.',
  wrongPassword: 'Ievadītā parole nav pareiza.',
  usernameTooLong: 'Lietotājvārdam jābūt ne vairāk kā 10 rakstzīmes garam.',
  saveBook: 'Grāmatas izmaiņas neizdevās saglabāt. Mēģiniet vēlreiz.',
  saveGenre: 'Žanra izmaiņas neizdevās saglabāt. Mēģiniet vēlreiz.',
  saveProfile: 'Profila izmaiņas neizdevās saglabāt. Mēģiniet vēlreiz.',
  server: 'Serverī radās kļūda. Mēģiniet vēlreiz vēlāk.',
  sessionExpired: 'Jūsu sesija ir beigusies. Lūdzu, pieslēdzieties vēlreiz.',
  uploadAvatar: 'Profila attēlu neizdevās augšupielādēt. Mēģiniet vēlreiz.',
  validation: 'Lūdzu, pārbaudiet atzīmētos laukus.',
});

const fieldNames = {
  ISBN: 'ISBN',
  Nodala_ID: 'nodaļa',
  Zanra_ID: 'žanrs',
  autors: 'autors',
  bio: 'biogrāfija',
  book_id: 'grāmata',
  current_password: 'pašreizējā parole',
  dzim_datums: 'dzimšanas datums',
  epasts: 'e-pasta adrese',
  faila_pdf: 'PDF fails',
  foto: 'profila attēls',
  gads: 'izdošanas gads',
  gramatas_id: 'grāmata',
  isbn: 'ISBN',
  komentars: 'komentārs',
  lapu_skaits: 'lapu skaits',
  lietotaja_vards: 'lietotājvārds',
  new_password: 'jaunā parole',
  new_password_confirmation: 'jaunās paroles apstiprinājums',
  nosaukums: 'nosaukums',
  parole: 'parole',
  password: 'parole',
  pilseta: 'pilsēta',
  status: 'statuss',
  statuss: 'statuss',
  vecakais_komentars: 'vecākais komentārs',
  vertejums: 'vērtējums',
  vaku_attels: 'vāka attēls',
};

const apiErrorMessageKeys = {
  'Jūsu konts ir bloķēts. Sazinieties ar administratoru.': 'accountBlocked',
  'Jūsu sesija ir beigusies. Lūdzu, pieslēdzieties vēlreiz.': 'sessionExpired',
  'Lietotājs nav autentificēts': 'sessionExpired',
  'Nav autentificēts': 'sessionExpired',
  'Nepareizs e-pasts vai parole': 'login',
  'Nepareiza parole': 'wrongPassword',
  'Pašreizējā parole nav pareiza': 'wrongPassword',
  'Piekļuve liegta': 'accessDenied',
  'Šī grāmata jau ir jūsu bibliotēkā': 'duplicateBook',
};

function translateValidationMessage(field, message) {
  if (typeof message !== 'string') return ERROR_MESSAGES.invalidData;

  const name = fieldNames[field] || field;
  let match = message.match(/^The .+ field is required\.$/i);
  if (match) return `Lauks ${name} ir obligāts.`;

  match = message.match(/^The .+ field must be a valid email address\.$/i);
  if (match) return `Laukam ${name} jābūt derīgai e-pasta adresei.`;

  match = message.match(/^The .+ field has already been taken\.$/i);
  if (match) return `Šāda ${name} vērtība jau tiek izmantota.`;

  match = message.match(/^The .+ field must be at least (\d+) characters?\.$/i);
  if (match) return `Laukam ${name} jābūt vismaz ${match[1]} rakstzīmēm garam.`;

  match = message.match(/^The .+ field must not be greater than (\d+) characters?\.$/i);
  if (match) return `Lauks ${name} nedrīkst būt garāks par ${match[1]} rakstzīmēm.`;

  match = message.match(/^The .+ field must be an integer\.$/i);
  if (match) return `Laukam ${name} jābūt veselam skaitlim.`;

  match = message.match(/^The .+ field must be a string\.$/i);
  if (match) return `Laukam ${name} jābūt tekstam.`;

  match = message.match(/^The .+ field confirmation does not match\.$/i);
  if (match) return `Lauka ${name} apstiprinājums nesakrīt.`;

  return /\b(the|field|must|required|invalid|failed|error)\b/i.test(message)
    ? ERROR_MESSAGES.invalidData
    : message;
}

export function getValidationMessages(errors) {
  if (!errors || typeof errors !== 'object') return [];

  return Object.entries(errors).flatMap(([field, messages]) => {
    const values = Array.isArray(messages) ? messages : [messages];
    return values.map((message) => translateValidationMessage(field, message));
  });
}

export function getValidationErrorsByField(errors) {
  if (!errors || typeof errors !== 'object') return {};

  return Object.fromEntries(
    Object.entries(errors).map(([field, messages]) => {
      const values = Array.isArray(messages) ? messages : [messages];
      return [field, values.map((message) => translateValidationMessage(field, message))];
    })
  );
}

export function getApiErrorMessage(data, fallback = ERROR_MESSAGES.generic) {
  const validationMessages = getValidationMessages(data?.errors);
  if (validationMessages.length) return validationMessages.join(' ');

  const message = data?.message;
  if (typeof message === 'string' && message.trim()) {
    const messageKey = apiErrorMessageKeys[message.trim()];
    return messageKey ? ERROR_MESSAGES[messageKey] : fallback;
  }

  return fallback;
}