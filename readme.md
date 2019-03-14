**** **T-Shirt Shop**

Dit project werd gemaakt in opdracht van Rene Krewinkel en handeld de betaling van een web order af. 

**** **USED PATTERNS IN THIS APPLICATION**

 * Singleton in App/Factory namespace to connect the database.
 * Observers voor verwerking
 * Adapter voor email verzending (App/Confirmation.php)
 * Abstract Class voor de Observer
 * Interface voor de client Class so we're sure that all information is in it :)
 * Factory in Factory.php voor bijvoorbeeld gebruik Singleton
 * Trait in de observer to save all kind of data in a txt file.

De patterns worden door geheel de applicatie verwerkt om de bestelling af te handelen conform de opdracht. https://workshops.studiokrijst.com/vodafone-ziggo/downloads/vfzg-case-masterclass.pdf

**** **STAPPEN**

De Observer is de verzamelaar van data welke aan het einde (in de laatste observer) nodig is om een bevestiging te verzenden aan de klant. Natuurlijk kan je dit allemaal opnieuw querieen in de database, maar de alle observers verzamelen voldoende informatie welke opgeslagen kunnen worden voor later gebruik.
Stap 8 & 9 heb ik omgedraaid omdat dat logischer is :) Je maakt pas een sticker bij verzending en daarvoor dien je eerst de controle in het magazijn te doen.
 
Stap 11 & 12 heb ik smaen gevoegd omdat een stukje software geen order kan inpakken :) Hiervoor heb je nog steeds mensenhanden nodig. Dus de verzending zal alle verzamelde data tijdens het gehele proces in een bevesitging naar de klant sturen.  
 
- Stap 05: Bevestiging Sturen: ConfirmationObserver (App/Observers/ConfirmationObserver.php)
- Stap 06: Factuur Aanmaken: (App/Observers/InvoiceObserver.php)
- Stap 07: Creditfactuur aanmaken: InvoiceObserver (App/Observers/InvoiceObserver.php)
- Stap 08: PostNL sticker maken: TransportObserver (App/Observers/TransportObserver.php)
- Stap 09: Super simpele stock check ivm tijdsgebrek en drukte op het werk (App/Observers/TransportObserver.php)
- Stap 10: Geen stock? Vervelend - De API van de leerancier is altijd open. (App/Observers/TransportObserver.php && App/Stock.php)
- Stap 11: Verzending Bevestiging 

***** **Singleton**

Deze wordt gebruikt in de App/Factory nnamespace om een Singleton database connectie op te zetten, de singleton database wordt voor elke transactie opnieuw gebruikt om een flood aan database connecties te voorkomen.
 
 ***** **Observer**
 
 De observer wordt gebruikt in de ipn.php (start van de applicatie) en flow't door alles heen om alle acties onafhankelkijk van elkaar te kunnen verwerken. Stap 6 & 7 zitten bij elkaar in omdat deze enorm veel relaties met elkaar hebben.
 Elke observer kan iets toevoegen aan de transaction welke gestart is zodat je eventueel gegevens uit de transaction kan opvragen en dit naar een klant kan slingeren omdat er iets mis is gegaan.

 ***** **Adapter**
 
 De adapter wordt gebruikt in App/Confirmation.php om de voornaam en achternaam aan elkaar te koppelen. Dit wordt gedaan dmv de adapter. Daarna wordt het object terug gegeven aan de Observer welke deze data beschikbaar maakt binnen de gehele transactie.
 
 **** **USED ERD**
 
 ![Alt text](images/masterclass.jpg?raw=true "erd")