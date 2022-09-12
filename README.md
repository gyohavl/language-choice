# Volba druhého cizího jazyka

Webová aplikace [Gymnázia Olgy Havlové](https://www.gyohavl.cz/) pro přijaté uchazeče o studium. Autorem je [Vít Kološ](https://github.com/vitkolos).

## Instalace

Pro instalaci stačí zkopírovat adresář na PHP server, otevřít ve webovém prohlížeči stránku `/admin/` a projít celý proces. Při instalaci vzniká soubor `config.php`, v němž jsou uloženy údaje pro připojení k databázi a administrátorské heslo.

### Konfigurace přístupu k databázi

Způsob přístupu k databázi lze upravit v souboru `src/components/db.php` – může být potřeba změnit např. kódování (charset/collate) nebo předpony názvů tabulek (výchozí hodnota je `lc_`).

### Odesílání e-mailů

K odesílání e-mailů je použit [PHPMailer](https://github.com/PHPMailer/PHPMailer/), samotné odeslání probíhá prostřednictvím SMTP serveru. Může být potřeba povolit PHP rozšíření OpenSSL.
