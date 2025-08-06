const getOrganizationsByName = name => {
  return fetch(`https://data.brreg.no/enhetsregisteret/api/enheter/?navn=${name}`)
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      return data;
    });
}

const getOrganizationsByOrganizationNumber = organizationNumber => {
  return fetch(`https://data.brreg.no/enhetsregisteret/api/enheter/?organisasjonsnummer=${organizationNumber}`)
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      return data;
    });
}

const clearOrganizationSearchResultsDropdown = () => {
  const containerElement = document.getElementById('organizationSearchResultsDropdown');
  containerElement.classList.remove('hasContent');
  containerElement.innerHTML = "";
}

const clearSelectedOrganization = () => {
  document.getElementById('organizationNumber').value = "";
}

const selectOrganization = organization => {
  document.getElementById('organizationSearch').value = organization.navn;
  document.getElementById('organizationNumber').value = organization.organisasjonsnummer;
  clearOrganizationSearchResultsDropdown();
}


const populateOrganizationSearchResultsDropdown = searchResults => {
  const containerElement = document.getElementById('organizationSearchResultsDropdown');
  containerElement.classList.add('hasContent');
  containerElement.innerHTML = "";
  searchResults.forEach(searchResult => {
    let resultElement = document.createElement("DIV");
    resultElement.setAttribute("class", "result");

    let resultNameElement = document.createElement("SPAN");
    resultNameElement.setAttribute("class", "resultName");
    resultNameElement.innerHTML = searchResult.navn;

    let resultOrganizationNumberElement = document.createElement("SPAN");
    resultOrganizationNumberElement.setAttribute("class", "resultOrganizationNumber");
    resultOrganizationNumberElement.innerHTML = searchResult.organisasjonsnummer;

    resultElement.appendChild(resultNameElement);
    resultElement.appendChild(resultOrganizationNumberElement);

    resultElement.onclick = () => {
      selectOrganization(searchResult)
    };

    containerElement.appendChild(resultElement);
  })
}

const isFormattedAsOrganizationNumber = string => {
  return !isNaN(string) && string.length === 9;
}

const searchResultsContainsItems = searchResults => {
  return searchResults._embedded && searchResults._embedded.enheter && searchResults._embedded.enheter.length;
}

const handleOrganizationOnSearch = event => {
  if (!event.target.value) { // On clear search click (X)
    clearOrganizationSearchResultsDropdown();
  }
}

const handleOrganizationSearchChange = searchString => {
  clearSelectedOrganization();
  if (searchString.length) {
    isFormattedAsOrganizationNumber(searchString.replace(/\s/g, '')) ?
      getOrganizationsByOrganizationNumber(searchString.replace(/\s/g, '')).then(searchResults => {
        searchResultsContainsItems(searchResults) ? populateOrganizationSearchResultsDropdown(searchResults._embedded.enheter) : clearOrganizationSearchResultsDropdown();
      }) :
      getOrganizationsByName(searchString).then(searchResults => {
        searchResultsContainsItems(searchResults) ? populateOrganizationSearchResultsDropdown(searchResults._embedded.enheter) : clearOrganizationSearchResultsDropdown();
      });
  } else {
    clearOrganizationSearchResultsDropdown();
  }
}

const handleOrganizationSearchBlur = () => {
  setTimeout(clearOrganizationSearchResultsDropdown, 300);
}
