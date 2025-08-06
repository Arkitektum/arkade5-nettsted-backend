getReleases("arkivverket", "arkade5").then(releases => {
  releases.forEach((release, index) => {
    if (index === 0) {
      let containerElement = document.getElementById('latestRelease');
      let releaseElement = createReleaseElement(release, true);
      containerElement.classList.add('accordionList');
      containerElement.appendChild(releaseElement);
    } else {
      let containerElement = document.getElementById('previousReleases');
      let releaseElement = createReleaseElement(release, false);
      containerElement.classList.add('accordionList');
      containerElement.appendChild(releaseElement);
    }
  });
});
