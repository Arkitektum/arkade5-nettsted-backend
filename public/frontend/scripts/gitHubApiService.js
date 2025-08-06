const getReleases = (owner, repo) => {
  return fetch('https://api.github.com/repos/arkivverket/arkade5/releases')
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      return data;
    });
}

const getLatestRelease = (owner, repo) => {
  return fetch('https://api.github.com/repos/arkivverket/arkade5/releases/latest')
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      return data;
    });
}
