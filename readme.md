**** **T-Shirt Shop**

Dit project werd gemaakt in opdracht van Rene Krewinkel en handeld de betaling van een web order af.

**** **USED PATTERNS IN THIS APPLICATION**

 * Singleton in App/Factory namespace to connect the database.
 * Observers voor verwerking
 * Adapter voor email verzending
 * Abstract Class voor de Observer
 * Interface voor de client Class so we're sure that all information is in it :)
 * Factory in Factory.php voor bijvoorbeeld gebruik Singleton
 * Trait in de observer to save all kind of data in a txt file.

De patterns worden door geheel de applicatie verwerkt om de bestelling af te handelen conform de opdracht. https://workshops.studiokrijst.com/vodafone-ziggo/downloads/vfzg-case-masterclass.pdf

**** **STAPPEN**

Stap 8 & 9 heb ik omgedraaid omdat dat logischer is :) Je maakt pas een sticker bij verzending en daarvoor dien je eerst de controle in het magazijn te doen.
 
- Stap 5: Bevestiging Sturen: ConfirmationObserver (App/Observers/ConfirmationObserver.php)
- Stap 6: Factuur Aanmaken: (App/Observers/InvoiceObserver.php)
- Stap 7: Creditfactuur aanmaken: InvoiceObserver (App/Observers/InvoiceObserver.php)
- Stap 8: PostNL sticker maken: TransportObserver (App/Observers/TransportObserver.php)


***** **Singleton**

Deze wordt gebruikt in de App/Factory nnamespace om een Singleton database connectie op te zetten, de singleton database wordt voor elke transactie opnieuw gebruikt om een flood aan database connecties te voorkomen.
 
 ***** **Observer**
 
 De observer wordt gebruikt in de ipn.php (start van de applicatie) en flow't door alles heen om alle acties onafhankelkijk van elkaar te kunnen verwerken. Stap 6 & 7 zitten bij elkaar in omdat deze enorm veel relaties met elkaar hebben.
 Elke observer kan iets toevoegen aan de transaction welke gestart is zodat je eventueel gegevens uit de transaction kan opvragen en dit naar een klant kan slingeren omdat er iets mis is gegaan.
 
 **** **USED ERD**
 
 ![Alt text](images/masterclass.jpg?raw=true "Title")