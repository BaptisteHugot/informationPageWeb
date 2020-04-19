/**
* @file graph.js
* @brief Définit les graphiques qui seront affichés sur la page de statistiques
*/

/**
* Fonction définissant le graphe représentant le nombre de sites par "étoiles" IPv6
*/
function showGraphNbSitesParEtoile()
{
  {
    $.post("data.php",
    {action: 'nbSitesParEtoile'},
    function (data)
    {
      console.log(data);
      var resultIPv6 = [];
      var nbDomain = [];

      for (var i in data) {
        resultIPv6.push(data[i].resultIPv6);
        nbDomain.push(data[i].nbDomain);
      }

      var chartdata = {
        labels: resultIPv6,
        datasets: [
          {
            label: 'Nombre d\'étoiles',
            backgroundColor: '#49e2ff',
            borderColor: '#46d5f1',
            hoverBackgroundColor: '#CCCCCC',
            hoverBorderColor: '#666666',
            data: nbDomain
          }
        ]
      };

      var html = "<table border='1|1'>";
      for (var i = 0; i < data.length; i++) {
        html+="<tr>";
        html+="<td>"+data[i].resultIPv6+"</td>";
        html+="<td>"+data[i].nbDomain+"</td>";
        html+="</tr>";

      }
      html+="</table>";
      document.getElementById("tableau").innerHTML = html;

      var graphTarget = $("#graphCanvasNbSitesParEtoile");

      var barGraph = new Chart(graphTarget, {
        type: 'bar',
        data: chartdata,
        responsive: true,
        options: {
          scales: {
            yAxes: [{
              ticks: {
                min: 0,
                stepSize: 1
              }
            }]
          },
          title: {
            display: true,
            text: "Nombre d'étoiles au test IPv6"
          }
        }
      });
    });
  }
}

/**
* Fonction définissant le graphe représentant la répartition du nombre de sites testés lors des 30 derniers jours
*/
function showGraphNbSitesTestes30Jours()
{
  {
    $.post("data.php",
    {action: 'nbSitesTestes30Jours'},
    function (data)
    {
      console.log(data);
      var dateFormat = [];
      var nbTests = [];

      for (var i in data) {
        dateFormat.push(data[i].dateFormat);
        nbTests.push(data[i].nbTests);
      }

      var chartdata = {
        labels: dateFormat,
        datasets: [
          {
            label: 'Date',
            backgroundColor: '#49e2ff',
            borderColor: '#46d5f1',
            hoverBackgroundColor: '#CCCCCC',
            hoverBorderColor: '#666666',
            data: nbTests
          }
        ]
      };

      var html = "<table border='1|1'>";
      for (var i = 0; i < data.length; i++) {
        html+="<tr>";
        html+="<td>"+data[i].dateFormat+"</td>";
        html+="<td>"+data[i].nbTests+"</td>";
        html+="</tr>";

      }
      html+="</table>";
      document.getElementById("tableau").innerHTML = html;

      var graphTarget = $("#graphCanvasNbSitesTestes30Jours");

      var barGraph = new Chart(graphTarget, {
        type: 'bar',
        data: chartdata,
        responsive: true,
        options: {
          scales: {
            yAxes: [{
              ticks: {
                min: 0,
                stepSize: 1
              }
            }]
          },
          title: {
            display: true,
            text: "Nombre de sites testés lors des 30 derniers jours"
          }
        }
      });
    });
  }
}

/**
* Fonction définissant le graphe représentant la répartition mensuelle du nombre de sites testés
*/
function showGraphNbSitesTestesMois()
{
  {
    $.post("data.php",
    {action: 'nbSitesTestesMois'},
    function (data)
    {
      console.log(data);
      var dateFormat = [];
      var nbTests = [];

      for (var i in data) {
        dateFormat.push(data[i].dateFormat);
        nbTests.push(data[i].nbTests);
      }

      var chartdata = {
        labels: dateFormat,
        datasets: [
          {
            label: 'Date',
            backgroundColor: '#49e2ff',
            borderColor: '#46d5f1',
            hoverBackgroundColor: '#CCCCCC',
            hoverBorderColor: '#666666',
            data: nbTests
          }
        ]
      };

      var html = "<table border='1|1'>";
      for (var i = 0; i < data.length; i++) {
        html+="<tr>";
        html+="<td>"+data[i].dateFormat+"</td>";
        html+="<td>"+data[i].nbTests+"</td>";
        html+="</tr>";

      }
      html+="</table>";
      document.getElementById("tableau").innerHTML = html;

      var graphTarget = $("#graphCanvasNbSitesTestesMois");

      var barGraph = new Chart(graphTarget, {
        type: 'bar',
        data: chartdata,
        responsive: true,
        options: {
          scales: {
            yAxes: [{
              ticks: {
                min: 0,
                stepSize: 1
              }
            }]
          },
          title: {
            display: true,
            text: "Répartition mensuelle des sites testés"
          }
        }
      });
    });
  }
}

/**
* Fonction définissant le graphe représentant la répartition annuelle du nombre de sites testés
*/
function showGraphNbSitesTestesAn()
{
  {
    $.post("data.php",
    {action: 'nbSitesTestesAn'},
    function (data)
    {
      console.log(data);
      var dateFormat = [];
      var nbTests = [];

      for (var i in data) {
        dateFormat.push(data[i].dateFormat);
        nbTests.push(data[i].nbTests);
      }

      var chartdata = {
        labels: dateFormat,
        datasets: [
          {
            label: 'Date',
            backgroundColor: '#49e2ff',
            borderColor: '#46d5f1',
            hoverBackgroundColor: '#CCCCCC',
            hoverBorderColor: '#666666',
            data: nbTests
          }
        ]
      };

      var html = "<table border='1|1'>";
      for (var i = 0; i < data.length; i++) {
        html+="<tr>";
        html+="<td>"+data[i].dateFormat+"</td>";
        html+="<td>"+data[i].nbTests+"</td>";
        html+="</tr>";

      }
      html+="</table>";
      document.getElementById("tableau").innerHTML = html;

      var graphTarget = $("#graphCanvasNbSitesTestesAn");

      var barGraph = new Chart(graphTarget, {
        type: 'bar',
        data: chartdata,
        responsive: true,
        options: {
          scales: {
            yAxes: [{
              ticks: {
                min: 0,
                stepSize: 1
              }
            }]
          },
          title: {
            display: true,
            text: "Répartition annuelle des sites testés"
          }
        }
      });
    });
  }
}

/**
* Fonction définissant le graphe représentant la répartition mensuelle cumulée du nombre de sites testés
*/
function showGraphNbSitesTestesMoisCumul()
{
  {
    $.post("data.php",
    {action: 'nbSitesTestesMoisCumul'},
    function (data)
    {
      console.log(data);
      var dateFormat = [];
      var nbTests = [];

      for (var i in data) {
        dateFormat.push(data[i].dateFormat);
        nbTests.push(data[i].nbTests);
      }

      var chartdata = {
        labels: dateFormat,
        datasets: [
          {
            label: 'Date',
            backgroundColor: '#49e2ff',
            borderColor: '#46d5f1',
            hoverBackgroundColor: '#CCCCCC',
            hoverBorderColor: '#666666',
            data: nbTests
          }
        ]
      };

      var html = "<table border='1|1'>";
      for (var i = 0; i < data.length; i++) {
        html+="<tr>";
        html+="<td>"+data[i].dateFormat+"</td>";
        html+="<td>"+data[i].nbTests+"</td>";
        html+="</tr>";

      }
      html+="</table>";
      document.getElementById("tableau").innerHTML = html;

      var graphTarget = $("#graphCanvasNbSitesTestesMoisCumul");

      var barGraph = new Chart(graphTarget, {
        type: 'bar',
        data: chartdata,
        responsive: true,
        options: {
          scales: {
            yAxes: [{
              ticks: {
                min: 0,
                stepSize: 1
              }
            }]
          },
          title: {
            display: true,
            text: "Répartition mensuelle cumulée des sites testés"
          }
        }
      });
    });
  }
}

/**
* Fonction définissant le graphe représentant la répartition annuelle cumulée du nombre de sites testés
*/
function showGraphNbSitesTestesAnCumul()
{
  {
    $.post("data.php",
    {action: 'nbSitesTestesAnCumul'},
    function (data)
    {
      console.log(data);
      var dateFormat = [];
      var nbTests = [];

      for (var i in data) {
        dateFormat.push(data[i].dateFormat);
        nbTests.push(data[i].nbTests);
      }

      var chartdata = {
        labels: dateFormat,
        datasets: [
          {
            label: 'Date',
            backgroundColor: '#49e2ff',
            borderColor: '#46d5f1',
            hoverBackgroundColor: '#CCCCCC',
            hoverBorderColor: '#666666',
            data: nbTests
          }
        ]
      };

      var html = "<table border='1|1'>";
      for (var i = 0; i < data.length; i++) {
        html+="<tr>";
        html+="<td>"+data[i].dateFormat+"</td>";
        html+="<td>"+data[i].nbTests+"</td>";
        html+="</tr>";

      }
      html+="</table>";
      document.getElementById("tableau").innerHTML = html;

      var graphTarget = $("#graphCanvasNbSitesTestesAnCumul");

      var barGraph = new Chart(graphTarget, {
        type: 'bar',
        data: chartdata,
        responsive: true,
        options: {
          scales: {
            yAxes: [{
              ticks: {
                min: 0,
                stepSize: 1
              }
            }]
          },
          title: {
            display: true,
            text: "Répartition annuelle cumulée des sites testés"
          }
        }
      });
    });
  }
}
