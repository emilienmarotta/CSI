document.addEventListener("DOMContentLoaded", function () {
  fetch("api/getUsers.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur lors de la récupération des données");
      }
      return response.json();
    })
    .then((data) => {
      data.forEach((element) => {
        console.log(element);
      });
    });
});
