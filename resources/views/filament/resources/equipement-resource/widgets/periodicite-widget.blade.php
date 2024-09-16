<x-filament-widgets::widget>
    <x-filament::section>
        1 = MENSUELLE |
        2 = BIMESTRIELLE |
        3 = TRIMESTRIELLE |
        6 = SEMESTRIELLE |
        12 = ANNUELLE

        <br>
        <br>

        <p style="color:red"> Assurez-vous que pour votre fichier excel, les dates soient toutes au format date (ex:
            30/05/2024). Assurez-vous également du format des autres colonnes.</p> 
        
            <br>

            <p style="color:red">Tout le fichier sera parcouru et les lignes correctes seront importées. Les lignes erronnées seront omises et reportées dans  un fichier récupérable en cliquant sur le bouton RAPPORT D'ERREURS.</p>

        
            <br>

        <x-filament::button href="files/example_file.xlsx" tag="a">
            Télécharger un fichier exemple ici
        </x-filament::button>


    </x-filament::section>
</x-filament-widgets::widget>
