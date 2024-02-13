document.addEventListener('input', numeroExisteBouton);

// Il s’agie d’un script permettant de vérifier si le numero est valide pour pouvoir utiliser le bouton
function numeroExisteBouton() { // JD : Vérifie si le numero fournir est valide
    bontonNum = document.getElementById("testnumeroattribuerBouton");
    numTest = document.getElementById("testnumeroattribuer").value;
    bontonNum.disabled = !((numTest.length == '+33000000000'.length && numTest.startsWith("+33")) || ( numTest.length == '0600000000'.length && numTest.startsWith("0")));
}

// Il permet d'ouvrir le popup page.php pour tester le numero
function numeroExiste() {
    numTest = document.getElementById("testnumeroattribuer").value;
    popupWidth = document.getElementById("popupWidth").value;
    popupHeight = document.getElementById("popupHeight").value;
    if (numTest.startsWith("0")) {
        fistNumTest = numTest[0];
        numTest = numTest.replace(fistNumTest, "+33");
    }
    window.open("../plugins/popup/front/page.php?num=" + numTest, "", "width=" + popupWidth + ",height=" + popupHeight);
}

// Il affiche les numeros doublons avec la page doublons.php
function numeroDoublon() { // JD : affiche la popup des doublons
    popupWidth = document.getElementById("popupWidth").value;
    popupHeight = document.getElementById("popupHeight").value;
    window.open("../plugins/popup/front/doublons.php", "", "width=" + popupWidth + ",height=" + popupHeight);
}

// Il permet d'afficher une page sous la forme d'un popup
function redirectPage($url) { // JD : affiche la popup des doublons
    window.open($url, "", "width=" + window.innerWidth + ",height=" + window.innerHeight);
}

// Il permet de sois affiché la page confirm si c'est un standard ou la page insert si sait un utilisateur lors
// de l'ajout un numéro inconnu
function submitFormPage()
{
    typeEntity = document.getElementById("typeEntity").value;
    if(typeEntity === 'standard')
    {
        document.getElementById("formPagePopup").action = "confirm.php";
    } else if(typeEntity === 'utilisateur')
    {
        document.getElementById("formPagePopup").action ="insert.php";
    }
    //console.log(document.getElementById("typeEntity").value);
    //console.log(document.getElementById("formPagePopup").action);
    return true;
}

// Il sert à confirmer l'ajout du numero de téléphone et d'agir en fonction du type a la personne à qui été ajouté
function inputBoutonConfirm($type){
    if ($type === 'standard'){
       return "header('Location: insert.php?num2='.$_GET['num2'].'&entities='.$_GET['entities'].'&type='.$_GET['type'].'&num='.$_GET['num']);";
    }else{
        return "header('Location: insert3.php?utilisateur='.$_GET['utilisateur'].'&num2='.$_GET['num2'].'&numamaj='.$_GET['numamaj'].'&num='.$_GET['num']);";
    }
}

// Il sert à lancer la page de sauvegarde de configuration
async function saveConfigPopup() { // JD : purge la popup des inconnus
    $string = confirm("Confirmez-vous la sauvegarde des paramètres");
    if ($string === true){
        console.log(document.getElementById("popupLink").value);
        link = document.getElementById("popupLink").value;
        popupWidth = document.getElementById("popupWidth").value;
        popupHeight = document.getElementById("popupHeight").value;
        $saveconfigwindow = window.open("../plugins/popup/front/saveconfig.php?" +
            "width="+popupWidth+
            "&height="+popupHeight+
            "&link="+link , "", "width=1,height=1");
        setTimeout(function(){$saveconfigwindow.close()}, 2000);
    }
}