const organizationMap = L.map('organization-map').setView([65.5, 16], 5);

L.tileLayer('https://cache.kartverket.no/v1/wmts/1.0.0/topo/default/webmercator/{z}/{y}/{x}.png', {
    attribution: 'Bakgrunnnskart og posisjonsdata fra © <a href="https://kartverket.no/" target="_blank">Kartverket</a>',
}).addTo(organizationMap);

const arkadeIcon = L.icon({
    iconUrl: 'favicon.ico',
    //shadowUrl: 'leaf-shadow.png',
    iconSize: [32, 32],
    //shadowSize:   [50, 64],
    iconAnchor: [16, 16],
    //shadowAnchor: [4, 62],
    popupAnchor: [0, -14]
});

const getOrganizations = () => {
    const apiSubdomain = 'backend';
    const apiHost = `${window.location.protocol}//${apiSubdomain}.${window.location.hostname}`;
    const apiUrl = `${apiHost}/api/organization-locations`;

    return fetch(apiUrl)
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            return data.data;
        });
}

getOrganizations().then(organizations => {

    document.getElementById("organizationCount").innerHTML = organizations.length;

    var organizationLayer = L.geoJSON(organizations, {
        pointToLayer: function (feature, latlng) {
            return L.marker(latlng, {icon: arkadeIcon, riseOnHover: true});
        },
        onEachFeature: function (feature, layer) {
            layer.bindPopup(feature.properties.organizationName);
        }
    });

    var clusteredOrganizationLayer = L.markerClusterGroup({
        maxClusterRadius: 0.1,
        spiderfyDistanceMultiplier: 1.5,
        spiderLegPolylineOptions: {weight: 3, color: '#419488', opacity: 1}
    }).addLayer(organizationLayer);

    organizationMap.addLayer(clusteredOrganizationLayer);

});
