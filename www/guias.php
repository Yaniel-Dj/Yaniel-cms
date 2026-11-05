<?php
require_once __DIR__ . '/lang.php';

$guias = [
  [
    'slug' => 'mineria',
    'color' => '#b8923e',
    'icono' => 'fa-mountain',
    'nombre' => ['es' => 'Minería', 'en' => 'Mining'],
    'niveles' => [
      ['1–65', ['es' => 'Durotar, Dun Morogh, Elwynn, Teldrassil, Mulgore, Tirisfal', 'en' => 'Durotar, Dun Morogh, Elwynn, Teldrassil, Mulgore, Tirisfal'], ['es' => 'Cobre. Funde hasta skill ~25, luego solo minar sube. Prioriza cuevas: Foso de las Sombras (Durotar), Cuevas de Dun Morogh, mina de Elwynn.', 'en' => 'Copper. Smelt until skill ~25, then mining alone levels you. Prioritize caves: Shadowfang Keep (Durotar), Dun Morogh caves, Elwynn mine.']],
      ['65–125', ['es' => 'Los Baldíos, Mil Agujas, Crestagrana, Bosque de Argénteos', 'en' => 'The Barrens, Thousand Needles, Redridge Mountains, Silverpine Forest'], ['es' => 'Estaño principal; la plata sale mezclada. Funde Bronce (cobre+estaño) hasta ~110 para subir rápido. Mil Agujas es la mejor zona.', 'en' => 'Mainly tin; silver comes mixed. Smelt Bronze (copper+tin) until ~110 to level fast. Thousand Needles is the best zone.']],
      ['125–175', ['es' => 'Tierras Altas de Arathi, Mil Agujas, Tierras del Interior', 'en' => 'Arathi Highlands, Thousand Needles, Hinterlands'], ['es' => 'Hierro. A 155 aprendes Oro (raro, sale pegado al hierro). Arathi es la zona reina: vetas abundantes y accesibles.', 'en' => 'Iron. At 155 you learn Gold (rare, spawns with iron). Arathi is the king zone: abundant and accessible veins.']],
      ['175–245', ['es' => 'Arathi, Tierras del Interior, Feralas, Tanaris', 'en' => 'Arathi, Hinterlands, Feralas, Tanaris'], ['es' => 'Mithril base hasta 245. A 230 aprendes Veraplata (rara). Combina Arathi para mithril, Feralas/Tanaris para veraplata.', 'en' => 'Mithril base until 245. At 230 you learn Truesilver (rare). Combine Arathi for mithril, Feralas/Tanaris for truesilver.']],
      ['245–275', ['es' => 'Frondavil, Cráter de Un\'Goro, Silithus', 'en' => 'Felwood, Un\'Goro Crater, Silithus'], ['es' => 'Aprendes Torio. Frondavil es lo más seguro (zona 45–55). Un\'Goro más vetas pero más mobs. Silithus reserva para después.', 'en' => 'You learn Thorium. Felwood is the safest (zone 45–55). Un\'Goro has more veins but more mobs. Save Silithus for later.']],
      ['275–300', ['es' => 'Silithus, Frondavil, Tierras de la Peste del Este', 'en' => 'Silithus, Felwood, Eastern Plaguelands'], ['es' => 'Torio puro. Silithus es la mejor zona pero es 55–60. Funde Barras de Torio: se venden bien en subasta.', 'en' => 'Pure Thorium. Silithus is the best zone but it is 55–60. Smelt Thorium Bars: they sell well at auction.']],
      ['300–325', ['es' => 'Península del Fuego Infernal, Marisma de Zangar', 'en' => 'Hellfire Peninsula, Zangarmarsh'], ['es' => 'Aprende Gran Maestro en Honor Hold / Thrallmar y empieza Fel Iron. Fundir sube hasta ~310.', 'en' => 'Learn Grand Master in Honor Hold / Thrallmar and start Fel Iron. Smelting levels you to ~310.']],
      ['325–350', ['es' => 'Nagrand, Espina de Sangre, Marisma de Zangar', 'en' => 'Nagrand, Blade\'s Edge Mountains, Zangarmarsh'], ['es' => 'Fel Iron hasta ~340 y luego Adamantita. Nagrand tiene cuevas con ambas menas juntas.', 'en' => 'Fel Iron until ~340 then Adamantite. Nagrand has caves with both ores together.']],
      ['350–375', ['es' => 'Nagrand, Valle Sombraluna, Espina de Sangre', 'en' => 'Nagrand, Shadowmoon Valley, Blade\'s Edge Mountains'], ['es' => 'Adamantita pura hasta 375. Sombraluna rinde más pero mobs 67–70; ve con cuidado.', 'en' => 'Pure Adamantite until 375. Shadowmoon yields more but mobs are 67–70; be careful.']],
      ['375–400', ['es' => 'Tundra Boreal, Fiordo Aquilonal', 'en' => 'Borean Tundra, Howling Fjord'], ['es' => 'Viaja a Rasganorte, aprende Gran Maestro en Valgarde / Vengeance Landing. Cobalto en Tundra Boreal (Fizzcrank Airstrip).', 'en' => 'Travel to Northrend, learn Grand Master in Valgarde / Vengeance Landing. Cobalt in Borean Tundra (Fizzcrank Airstrip).']],
      ['400–425', ['es' => 'Cuenca de Sholazar, Zul\'Drak, Colinas Pardas', 'en' => 'Sholazar Basin, Zul\'Drak, Grizzly Hills'], ['es' => 'Saronita. Sholazar es la mejor: mucho verde, poca hostilidad. El Titanio sale mezclado: guárdalo.', 'en' => 'Saronite. Sholazar is the best: lots of green, little hostility. Titanium comes mixed: save it.']],
      ['425–450', ['es' => 'Sholazar, Corona de Hielo, Cumbres Tormentosas', 'en' => 'Sholazar, Icecrown, Storm Peaks'], ['es' => 'Saronita hasta 450. El Titanio se vende muy caro. ¡Felicidades, 450 Minería!', 'en' => 'Saronite until 450. Titanium sells very high. Congratulations, 450 Mining!']],
    ]
  ],
  [
    'slug' => 'herboristeria',
    'color' => '#5a8a5e',
    'icono' => 'fa-leaf',
    'nombre' => ['es' => 'Herboristería', 'en' => 'Herbalism'],
    'niveles' => [
      ['1–70', ['es' => 'Durotar, Elwynn, Teldrassil, Mulgore, Tirisfal, Dun Morogh', 'en' => 'Durotar, Elwynn, Teldrassil, Mulgore, Tirisfal, Dun Morogh'], ['es' => 'Hierba paz y hoja de tierra. Aparecen casi en cualquier sitio de zona inicial; no vendas nada, todo sirve.', 'en' => 'Peacebloom and Earthroot. They appear almost anywhere in starting zones; do not sell anything, it all helps.']],
      ['70–115', ['es' => 'Mulgore, Los Baldíos, Bosque de Argénteos, Loch Modan', 'en' => 'Mulgore, The Barrens, Silverpine Forest, Loch Modan'], ['es' => 'Raíz de tierra y hoja de vida. Los Baldíos es la mejor por densidad.', 'en' => 'Earthroot and Mageroyal. The Barrens is the best for density.']],
      ['115–160', ['es' => 'Los Baldíos, Ashenvale, Trabalomas, Mil Agujas', 'en' => 'The Barrens, Ashenvale, Hillsbrad Foothills, Thousand Needles'], ['es' => 'Hierba bruja y espina de oro. Sigue en Baldíos hasta 130, luego salta a Ashenvale.', 'en' => 'Briarthorn and Goldthorn. Stay in Barrens until 130, then jump to Ashenvale.']],
      ['160–210', ['es' => 'Desolace, Tuercespina, Feralas, Tierras del Interior', 'en' => 'Desolace, Stranglethorn Vale, Feralas, Hinterlands'], ['es' => 'Loto cárdeno y hierba de fuego. Tuercespina es buena: hierbas junto a agua.', 'en' => 'Khadgar\'s Whisker and Firebloom. Stranglethorn is good: herbs near water.']],
      ['210–245', ['es' => 'Feralas, Tierras del Interior, Tuercespina', 'en' => 'Feralas, Hinterlands, Stranglethorn Vale'], ['es' => 'Hierba de ensueño y sangrerregia. Feralas tiene mucha densidad.', 'en' => 'Dreamfoil and Sungrass. Feralas has high density.']],
      ['245–280', ['es' => 'Feralas, Tuercespina, Tierras del Interior', 'en' => 'Feralas, Stranglethorn Vale, Hinterlands'], ['es' => 'Sangrerregia y hierba de ensueño. Si subes con Alquimia, guarda todo.', 'en' => 'Sungrass and Dreamfoil. If you level with Alchemy, save everything.']],
      ['280–300', ['es' => 'Frondavil, Un\'Goro, Silithus', 'en' => 'Felwood, Un\'Goro Crater, Silithus'], ['es' => 'Ginseng y loto cárdeno. Frondavil cómodo; Un\'Goro más denso pero peligroso.', 'en' => 'Golden Sansam and Khadgar\'s Whisker. Felwood is comfortable; Un\'Goro denser but dangerous.']],
      ['300–325', ['es' => 'Fuego Infernal, Marisma de Zangar', 'en' => 'Hellfire Peninsula, Zangarmarsh'], ['es' => 'Aprende Gran Maestro y empieza hierba vil. Zangar tiene mucho junco de esporas.', 'en' => 'Learn Grand Master and start Felweed. Zangar has lots of Sporecap.']],
      ['325–350', ['es' => 'Nagrand, Bosque de Terokkar', 'en' => 'Nagrand, Terokkar Forest'], ['es' => 'Hierba de maná y fuego vil. Nagrand es la más tranquila para farmear.', 'en' => 'Mana Thistle and Felweed. Nagrand is the calmest to farm.']],
      ['350–375', ['es' => 'Nagrand, Tormenta Abisal', 'en' => 'Nagrand, Netherstorm'], ['es' => 'Hierba de maná y Campanilla de las Sombras. Sigue en Nagrand hasta 375.', 'en' => 'Mana Thistle and Shadowbell. Stay in Nagrand until 375.']],
      ['375–400', ['es' => 'Tundra Boreal, Fiordo Aquilonal', 'en' => 'Borean Tundra, Howling Fjord'], ['es' => 'Aprende Gran Maestro en Rasganorte y empieza roña de oro. Tundra es la mejor zona.', 'en' => 'Learn Grand Master in Northrend and start Goldclover. Tundra is the best zone.']],
      ['400–425', ['es' => 'Sholazar, Zul\'Drak', 'en' => 'Sholazar Basin, Zul\'Drak'], ['es' => 'Adelfilla y loto helado. Sholazar es la reina: mucho verde y poca amenaza.', 'en' => 'Tiger Lily and Frost Lotus. Sholazar is the queen: lots of green and little threat.']],
      ['425–450', ['es' => 'Sholazar, Corona de Hielo', 'en' => 'Sholazar, Icecrown'], ['es' => 'Loto helado y adelfilla. Guarda Loto helado: es oro puro en subasta.', 'en' => 'Frost Lotus and Tiger Lily. Save Frost Lotus: it is pure gold at auction.']],
    ]
  ],
  [
    'slug' => 'peleteria',
    'color' => '#7a6040',
    'icono' => 'fa-paw',
    'nombre' => ['es' => 'Peletería', 'en' => 'Skinning'],
    'niveles' => [
      ['1–75', ['es' => 'Durotar, Mulgore, Elwynn, Dun Morogh, Teldrassil', 'en' => 'Durotar, Mulgore, Elwynn, Dun Morogh, Teldrassil'], ['es' => 'Desuella bestias 5–15. Cuero ligero. Todo lo que veas con pelaje, desuéllelo.', 'en' => 'Skin beasts 5–15. Light Leather. Anything with fur, skin it.']],
      ['75–150', ['es' => 'Los Baldíos, Mil Agujas, Argénteos, Loch Modan', 'en' => 'The Barrens, Thousand Needles, Silverpine, Loch Modan'], ['es' => 'Cuero medio. Bestias 15–30: jabalíes, raptors, osos. Los Baldíos rinde mucho.', 'en' => 'Medium Leather. Beasts 15–30: boars, raptors, bears. The Barrens yields a lot.']],
      ['150–225', ['es' => 'Tuercespina, Tierras del Interior, Mil Agujas', 'en' => 'Stranglethorn Vale, Hinterlands, Thousand Needles'], ['es' => 'Cuero pesado. Bestias 30–45: raptors de Tuercespina son la mina de oro.', 'en' => 'Heavy Leather. Beasts 30–45: Stranglethorn raptors are the goldmine.']],
      ['225–275', ['es' => 'Feralas, Tanaris, Un\'Goro', 'en' => 'Feralas, Tanaris, Un\'Goro'], ['es' => 'Cuero grueso. Bestias 45–55. Un\'Goro tiene dinosaurios que dan cuero en abundancia.', 'en' => 'Thick Leather. Beasts 45–55. Un\'Goro has dinosaurs that give leather in abundance.']],
      ['275–300', ['es' => 'Silithus, Frondavil, Peste del Este', 'en' => 'Silithus, Felwood, Eastern Plaguelands'], ['es' => 'Cuero grueso y escamas. Silithus tiene insectos que sueltan quitina útil.', 'en' => 'Thick Leather and scales. Silithus has insects that drop useful chitin.']],
      ['300–330', ['es' => 'Fuego Infernal, Marisma de Zangar', 'en' => 'Hellfire Peninsula, Zangarmarsh'], ['es' => 'Cuero de Terrallende. Jabalíes infernales y bestias de Zangar.', 'en' => 'Outland Leather. Hellboars and Zangar beasts.']],
      ['330–375', ['es' => 'Nagrand, Terokkar, Sombraluna', 'en' => 'Nagrand, Terokkar, Shadowmoon'], ['es' => 'Cuero exótico. Nagrand es la mejor: clefthoofs por todas partes.', 'en' => 'Exotic Leather. Nagrand is the best: clefthoofs everywhere.']],
      ['375–405', ['es' => 'Tundra Boreal, Fiordo Aquilonal', 'en' => 'Borean Tundra, Howling Fjord'], ['es' => 'Cuero boreal. Bestias de Rasganorte: osos, lobos. Tundra es más fácil.', 'en' => 'Borean Leather. Northrend beasts: bears, wolves. Tundra is easier.']],
      ['405–450', ['es' => 'Sholazar, Zul\'Drak, Corona de Hielo', 'en' => 'Sholazar, Zul\'Drak, Icecrown'], ['es' => 'Cuero helado y escamas. Sholazar tiene bestias exóticas muy rentables.', 'en' => 'Arctic Leather and scales. Sholazar has exotic beasts very profitable.']],
    ]
  ],
  [
    'slug' => 'sastreria',
    'color' => '#7a5a86',
    'icono' => 'fa-scissors',
    'nombre' => ['es' => 'Sastrería', 'en' => 'Tailoring'],
    'niveles' => [
      ['1–75', ['es' => 'Cualquier capital', 'en' => 'Any capital'], ['es' => 'Tela de lino. La sacas de humanoides nivel 5–15 o compras en subasta. Empieza con camisas y cinturones.', 'en' => 'Linen Cloth. Get it from humanoids level 5–15 or buy at auction. Start with shirts and belts.']],
      ['75–150', ['es' => 'Los Baldíos, Argénteos, Mil Agujas', 'en' => 'The Barrens, Silverpine, Thousand Needles'], ['es' => 'Tela de lana. Humanoides nivel 15–30. Funde y sube con bolsas de lana.', 'en' => 'Wool Cloth. Humanoids level 15–30. Level with wool bags.']],
      ['150–225', ['es' => 'Tuercespina, Tierras del Interior', 'en' => 'Stranglethorn Vale, Hinterlands'], ['es' => 'Seda y mageweave. Piratas de Tuercespina sueltan mucha seda.', 'en' => 'Silk and Mageweave. Stranglethorn pirates drop lots of silk.']],
      ['225–300', ['es' => 'Feralas, Tanaris, Silithus', 'en' => 'Feralas, Tanaris, Silithus'], ['es' => 'Tela de runa y tejido mágico. Humanoides 50–60. Feralas es la mejor zona.', 'en' => 'Runecloth and Mageweave. Humanoids 50–60. Feralas is the best zone.']],
      ['300–375', ['es' => 'Terrallende completo', 'en' => 'All Outland'], ['es' => 'Seda vil y tejido de hechicería. Compra en subasta si puedes; farmear tarda.', 'en' => 'Netherweave and Spellcloth. Buy at auction if you can; farming takes long.']],
      ['375–450', ['es' => 'Rasganorte completo', 'en' => 'All Northrend'], ['es' => 'Paño helado y tejido de escarcha. Sube con bolsas y capas básicas.', 'en' => 'Frostweave and Moonshroud. Level with bags and basic cloaks.']],
    ]
  ],
  [
    'slug' => 'herreria',
    'color' => '#6b7a8a',
    'icono' => 'fa-hammer',
    'nombre' => ['es' => 'Herrería', 'en' => 'Blacksmithing'],
    'niveles' => [
      ['1–75', ['es' => 'Capital + subasta', 'en' => 'Capital + auction'], ['es' => 'Barras de cobre y piedra rugiente. Compra cobre en subasta o mina tú mismo.', 'en' => 'Copper Bars and Rough Stone. Buy copper at auction or mine it yourself.']],
      ['75–150', ['es' => 'Subasta / mina', 'en' => 'Auction / mine'], ['es' => 'Barras de bronce y hierro. Funde bronce tú mismo (cobre+estaño).', 'en' => 'Bronze and Iron Bars. Smelt bronze yourself (copper+tin).']],
      ['150–225', ['es' => 'Subasta / mina', 'en' => 'Auction / mine'], ['es' => 'Barras de acero y mithril. Fundir con carbón. Necesitas Mina si no compras.', 'en' => 'Steel and Mithril Bars. Smelt with coal. You need Mining if you do not buy.']],
      ['225–300', ['es' => 'Frondavil / Silithus', 'en' => 'Felwood / Silithus'], ['es' => 'Barras de torio. Funde todo el torio que consigas: sube mucho skill.', 'en' => 'Thorium Bars. Smelt all thorium you get: it levels a lot.']],
      ['300–375', ['es' => 'Terrallende', 'en' => 'Outland'], ['es' => 'Barras de fel iron y adamantita. Compra en subasta o mina en Fuego Infernal.', 'en' => 'Fel Iron and Adamantite Bars. Buy at auction or mine in Hellfire.']],
      ['375–450', ['es' => 'Rasganorte', 'en' => 'Northrend'], ['es' => 'Barras de cobalto y saronita. Cobalto es barato; saronita más caro.', 'en' => 'Cobalt and Saronite Bars. Cobalt is cheap; saronite more expensive.']],
    ]
  ],
  [
    'slug' => 'joyeria',
    'color' => '#a05a86',
    'icono' => 'fa-gem',
    'nombre' => ['es' => 'Joyería', 'en' => 'Jewelcrafting'],
    'niveles' => [
      ['1–50', ['es' => 'Subasta', 'en' => 'Auction'], ['es' => 'Barras de cobre + gemas verdes baratas (ágata, ojo de tigre). Cuesta poco, sube rápido.', 'en' => 'Copper Bars + cheap green gems (agate, tiger eye). Costs little, levels fast.']],
      ['50–150', ['es' => 'Subasta', 'en' => 'Auction'], ['es' => 'Bronce, plata, hierro + gemas comunes. Anillos y collares básicos.', 'en' => 'Bronze, silver, iron + common gems. Basic rings and necklaces.']],
      ['150–225', ['es' => 'Subasta', 'en' => 'Auction'], ['es' => 'Mithril y oro + gemas verdes. Necesitas algo de oro inicial.', 'en' => 'Mithril and gold + green gems. You need some starting gold.']],
      ['225–300', ['es' => 'Subasta', 'en' => 'Auction'], ['es' => 'Torio + gemas de Azeroth. Tramo caro si no minas tú mismo.', 'en' => 'Thorium + Azeroth gems. Expensive stretch if you do not mine.']],
      ['300–375', ['es' => 'Terrallende + subasta', 'en' => 'Outland + auction'], ['es' => 'Fel iron + gemas de Terrallende. Corta gemas para subir.', 'en' => 'Fel Iron + Outland gems. Cut gems to level.']],
      ['375–450', ['es' => 'Rasganorte + subasta', 'en' => 'Northrend + auction'], ['es' => 'Cobalto, saronita + gemas de Rasganorte. Muy rentable al final por los epics.', 'en' => 'Cobalt, saronite + Northrend gems. Very profitable at the end due to epics.']],
    ]
  ],
  [
    'slug' => 'alquimia',
    'color' => '#5a8a8a',
    'icono' => 'fa-flask',
    'nombre' => ['es' => 'Alquimia', 'en' => 'Alchemy'],
    'niveles' => [
      ['1–60', ['es' => 'Zonas iniciales', 'en' => 'Starting zones'], ['es' => 'Hierba paz y hoja de tierra. Compra en subasta o recoge tú con Herboristería.', 'en' => 'Peacebloom and Earthroot. Buy at auction or gather with Herbalism.']],
      ['60–150', ['es' => 'Nivel medio', 'en' => 'Mid level'], ['es' => 'Raíz de tierra y hoja de vida. Pociones básicas de curación.', 'en' => 'Earthroot and Mageroyal. Basic healing potions.']],
      ['150–225', ['es' => 'Zona media-alta', 'en' => 'Mid-high zone'], ['es' => 'Hierba bruja y loto cárdeno. Elixires útiles para levear.', 'en' => 'Briarthorn and Khadgar\'s Whisker. Useful leveling elixirs.']],
      ['225–300', ['es' => 'Zona alta', 'en' => 'High zone'], ['es' => 'Hierba de ensueño y ginseng. Pociones de maná que se venden bien.', 'en' => 'Dreamfoil and Golden Sansam. Mana potions that sell well.']],
      ['300–375', ['es' => 'Terrallende', 'en' => 'Outland'], ['es' => 'Hierba vil y hierba de maná. Sube con elixires y transmutaciones.', 'en' => 'Felweed and Mana Thistle. Level with elixirs and transmutes.']],
      ['375–450', ['es' => 'Rasganorte', 'en' => 'Northrend'], ['es' => 'Roña de oro y loto helado. Frascos de raid son el objetivo final.', 'en' => 'Goldclover and Frost Lotus. Raid flasks are the final goal.']],
    ]
  ],
  [
    'slug' => 'encantamiento',
    'color' => '#6b6ba0',
    'icono' => 'fa-wand-magic-sparkles',
    'nombre' => ['es' => 'Encantamiento', 'en' => 'Enchanting'],
    'niveles' => [
      ['1–50', ['es' => 'Objetos verdes nivel 1–15', 'en' => 'Green items level 1–15'], ['es' => 'Desencanta verdes baratos de subasta. Polvo arcano.', 'en' => 'Disenchant cheap green items from auction. Arcane Dust.']],
      ['50–150', ['es' => 'Objetos verdes nivel 15–40', 'en' => 'Green items level 15–40'], ['es' => 'Esencia mágica. Compra verdes baratos o farmea humanoides.', 'en' => 'Magic Essence. Buy cheap greens or farm humanoids.']],
      ['150–225', ['es' => 'Objetos verdes 40–55', 'en' => 'Green items 40–55'], ['es' => 'Esencia astral y eterna. Los verdes suben de precio aquí.', 'en' => 'Astral and Eternal Essence. Green items go up in price here.']],
      ['225–300', ['es' => 'Objetos verdes 55–60', 'en' => 'Green items 55–60'], ['es' => 'Esencia ilusoria y onírica. Los verdes de 60 cuestan más.', 'en' => 'Illusion and Dream Dust. Level 60 greens cost more.']],
      ['300–375', ['es' => 'Terrallende', 'en' => 'Outland'], ['es' => 'Polvo abisal de verdes de Terrallende. Compra verdes al 60–70.', 'en' => 'Nether Dust from Outland greens. Buy greens at 60–70.']],
      ['375–450', ['es' => 'Rasganorte', 'en' => 'Northrend'], ['es' => 'Polvo infinito de verdes de Rasganorte. Tramo caro pero muy rentable al final.', 'en' => 'Infinite Dust from Northrend greens. Expensive stretch but very profitable at the end.']],
    ]
  ],
  [
    'slug' => 'ingenieria',
    'color' => '#8a6b4a',
    'icono' => 'fa-gears',
    'nombre' => ['es' => 'Ingeniería', 'en' => 'Engineering'],
    'niveles' => [
      ['1–75', ['es' => 'Mina o subasta', 'en' => 'Mine or auction'], ['es' => 'Barras de cobre + piedra rugiente. Bombas básicas.', 'en' => 'Copper Bars + Rough Stone. Basic bombs.']],
      ['75–150', ['es' => 'Subasta', 'en' => 'Auction'], ['es' => 'Bronce y hierro. Artefactos de nivel medio y munición.', 'en' => 'Bronze and Iron. Mid-level gadgets and ammo.']],
      ['150–225', ['es' => 'Subasta', 'en' => 'Auction'], ['es' => 'Acero y mithril. Mirones y aparatos útiles para PvP.', 'en' => 'Steel and Mithril. Goggles and useful PvP gadgets.']],
      ['225–300', ['es' => 'Subasta', 'en' => 'Auction'], ['es' => 'Torio + gemas y cueros. Robots y gafas de nivel 60.', 'en' => 'Thorium + gems and leather. Robots and level 60 goggles.']],
      ['300–375', ['es' => 'Terrallende', 'en' => 'Outland'], ['es' => 'Fel iron y adamantita. Empiezas a hacer gafas épicas.', 'en' => 'Fel Iron and Adamantite. Start making epic goggles.']],
      ['375–450', ['es' => 'Rasganorte', 'en' => 'Northrend'], ['es' => 'Cobalto y saronita. Gafas 200 y monturas míticas.', 'en' => 'Cobalt and Saronite. 200 goggles and mythic mounts.']],
    ]
  ],
  [
    'slug' => 'inscripcion',
    'color' => '#8a7048',
    'icono' => 'fa-scroll',
    'nombre' => ['es' => 'Inscripción', 'en' => 'Inscription'],
    'niveles' => [
      ['1–75', ['es' => 'Hierbas iniciales', 'en' => 'Starting herbs'], ['es' => 'Pigmentos de hierba paz y hoja de tierra. Necesitas Herboristería o subasta.', 'en' => 'Peacebloom and Earthroot pigments. You need Herbalism or auction.']],
      ['75–150', ['es' => 'Hierbas medias', 'en' => 'Mid herbs'], ['es' => 'Raíz de tierra y hoja de vida. Glifos menores.', 'en' => 'Earthroot and Mageroyal. Minor glyphs.']],
      ['150–225', ['es' => 'Hierbas medias-altas', 'en' => 'Mid-high herbs'], ['es' => 'Hierba bruja y loto cárdeno. Pergaminos de mejora.', 'en' => 'Briarthorn and Khadgar\'s Whisker. Enhancement scrolls.']],
      ['225–300', ['es' => 'Hierbas altas', 'en' => 'High herbs'], ['es' => 'Hierba de ensueño y ginseng. Glifos importantes.', 'en' => 'Dreamfoil and Golden Sansam. Important glyphs.']],
      ['300–375', ['es' => 'Terrallende', 'en' => 'Outland'], ['es' => 'Pigmentos de hierbas vil. Glifos para 70.', 'en' => 'Felweed pigments. Glyphs for 70.']],
      ['375–450', ['es' => 'Rasganorte', 'en' => 'Northrend'], ['es' => 'Pigmentos de Rasganorte. Glifos 80 y mejoras de hombro.', 'en' => 'Northrend pigments. 80 glyphs and shoulder enchants.']],
    ]
  ],
  [
    'slug' => 'desuello',
    'color' => '#7a4a3a',
    'icono' => 'fa-bone',
    'nombre' => ['es' => 'Desuello', 'en' => 'Skinning'],
    'niveles' => [
      ['1–75', ['es' => 'Zonas iniciales', 'en' => 'Starting zones'], ['es' => 'Bestias 5–15. Cuero ligero. Desuella TODO lo que mates.', 'en' => 'Beasts 5–15. Light Leather. Skin EVERYTHING you kill.']],
      ['75–150', ['es' => 'Los Baldíos, Mil Agujas, Argénteos, Loch Modan', 'en' => 'The Barrens, Thousand Needles, Silverpine, Loch Modan'], ['es' => 'Bestias 15–30. Cuero medio. Los Baldíos es la mina.', 'en' => 'Beasts 15–30. Medium Leather. The Barrens is the mine.']],
      ['150–225', ['es' => 'Tuercespina, Tierras del Interior, Mil Agujas', 'en' => 'Stranglethorn Vale, Hinterlands, Thousand Needles'], ['es' => 'Bestias 30–45. Cuero pesado. Raptors de Tuercespina rinden mucho.', 'en' => 'Beasts 30–45. Heavy Leather. Stranglethorn raptors yield a lot.']],
      ['225–275', ['es' => 'Feralas, Tanaris, Un\'Goro', 'en' => 'Feralas, Tanaris, Un\'Goro'], ['es' => 'Bestias 45–55. Cuero grueso. Un\'Goro con dinosaurios es ideal.', 'en' => 'Beasts 45–55. Thick Leather. Un\'Goro with dinosaurs is ideal.']],
      ['275–300', ['es' => 'Silithus, Frondavil, Peste del Este', 'en' => 'Silithus, Felwood, Eastern Plaguelands'], ['es' => 'Bestias 55–60. Cuero grueso y escamas.', 'en' => 'Beasts 55–60. Thick Leather and scales.']],
      ['300–375', ['es' => 'Terrallende', 'en' => 'Outland'], ['es' => 'Cuero de Terrallende. Clefthoofs de Nagrand son la fuente principal.', 'en' => 'Outland Leather. Nagrand clefthoofs are the main source.']],
      ['375–450', ['es' => 'Rasganorte', 'en' => 'Northrend'], ['es' => 'Cuero boreal y helado. Sholazar es la mejor zona.', 'en' => 'Borean and Arctic Leather. Sholazar is the best zone.']],
    ]
  ],
  [
    'slug' => 'cocina',
    'color' => '#a0703e',
    'icono' => 'fa-utensils',
    'nombre' => ['es' => 'Cocina', 'en' => 'Cooking'],
    'niveles' => [
      ['1–40', ['es' => 'Zonas iniciales', 'en' => 'Starting zones'], ['es' => 'Carne de jabalí y lobo. Cualquier bestia cercana a capital.', 'en' => 'Boar and Wolf Meat. Any beast near the capital.']],
      ['40–150', ['es' => 'Los Baldíos, Crestagrana', 'en' => 'The Barrens, Redridge'], ['es' => 'Carne de nivel medio. Compra recetas en capitales.', 'en' => 'Mid-level meat. Buy recipes in capitals.']],
      ['150–225', ['es' => 'Tierras del Interior, Tanaris', 'en' => 'Hinterlands, Tanaris'], ['es' => 'Carne 30–45. Recetas en Tantún / Gadgetzan.', 'en' => 'Meat 30–45. Recipes in Tanaris / Gadgetzan.']],
      ['225–300', ['es' => 'Feralas, Silithus', 'en' => 'Feralas, Silithus'], ['es' => 'Carne 45–60. Recetas de festín en Feralas.', 'en' => 'Meat 45–60. Feast recipes in Feralas.']],
      ['300–375', ['es' => 'Fuego Infernal, Nagrand', 'en' => 'Hellfire, Nagrand'], ['es' => 'Carne de Terrallende. Recetas de festín para grupos.', 'en' => 'Outland meat. Group feast recipes.']],
      ['375–450', ['es' => 'Tundra Boreal, Sholazar', 'en' => 'Borean Tundra, Sholazar'], ['es' => 'Carne de Rasganorte. Festines de raid son el objetivo.', 'en' => 'Northrend meat. Raid feasts are the goal.']],
    ]
  ],
  [
    'slug' => 'pesca',
    'color' => '#4a7090',
    'icono' => 'fa-fish',
    'nombre' => ['es' => 'Pesca', 'en' => 'Fishing'],
    'niveles' => [
      ['1–75', ['es' => 'Capitales y zonas iniciales', 'en' => 'Capitals and starting zones'], ['es' => 'Cualquier agua dulce sirve. Compra caña básica en el instructor.', 'en' => 'Any freshwater works. Buy a basic rod from the trainer.']],
      ['75–150', ['es' => 'Baldíos, Argénteos, Loch Modan', 'en' => 'Barrens, Silverpine, Loch Modan'], ['es' => 'Aguas medias. Peces para cocina media.', 'en' => 'Mid waters. Fish for mid cooking.']],
      ['150–225', ['es' => 'Tuercespina, Tierras del Interior, Tanaris', 'en' => 'Stranglethorn, Hinterlands, Tanaris'], ['es' => 'Aguas de nivel medio-alto. Sube con la caña que compraste.', 'en' => 'Mid-high waters. Level with the rod you bought.']],
      ['225–300', ['es' => 'Feralas, Silithus, Frondavil', 'en' => 'Feralas, Silithus, Felwood'], ['es' => 'Aguas de nivel alto. Peces raros para recetas.', 'en' => 'High-level waters. Rare fish for recipes.']],
      ['300–375', ['es' => 'Terrallende (Fuego Infernal, Nagrand, Zangar)', 'en' => 'Outland (Hellfire, Nagrand, Zangar)'], ['es' => 'Aguas de Terrallende. Peces para cocina 70.', 'en' => 'Outland waters. Fish for 70 cooking.']],
      ['375–450', ['es' => 'Rasganorte (Tundra, Sholazar, Corona)', 'en' => 'Northrend (Tundra, Sholazar, Icecrown)'], ['es' => 'Aguas heladas. Pez glacial y recetas de festín 80.', 'en' => 'Icy waters. Glacial fish and 80 feast recipes.']],
    ]
  ],
  [
    'slug' => 'primeros_auxilios',
    'color' => '#8a4a4a',
    'icono' => 'fa-kit-medical',
    'nombre' => ['es' => 'Primeros Auxilios', 'en' => 'First Aid'],
    'niveles' => [
      ['1–75', ['es' => 'Tela de lino', 'en' => 'Linen Cloth'], ['es' => 'Humanoides 5–15. Compra en subasta si no farmeas.', 'en' => 'Humanoids 5–15. Buy at auction if you do not farm.']],
      ['75–150', ['es' => 'Tela de lana', 'en' => 'Wool Cloth'], ['es' => 'Humanoides 15–30. Vendas gruesas de lana.', 'en' => 'Humanoids 15–30. Heavy Wool Bandages.']],
      ['150–225', ['es' => 'Tela de seda', 'en' => 'Silk Cloth'], ['es' => 'Humanoides 30–40. Vendas de seda.', 'en' => 'Humanoids 30–40. Silk Bandages.']],
      ['225–300', ['es' => 'Tela mageweave y runa', 'en' => 'Mageweave and Runecloth'], ['es' => 'Humanoides 40–60. Vendas de mageweave y runa.', 'en' => 'Humanoids 40–60. Mageweave and Runecloth Bandages.']],
      ['300–375', ['es' => 'Tela de seda vil', 'en' => 'Netherweave Cloth'], ['es' => 'Terrallende. Vendas de seda vil y antídotos.', 'en' => 'Outland. Netherweave Bandages and antidotes.']],
      ['375–450', ['es' => 'Paño helado', 'en' => 'Frostweave Cloth'], ['es' => 'Rasganorte. Vendas de escarcha para raids.', 'en' => 'Northrend. Frostweave Bandages for raids.']],
    ]
  ],
];

function g($texto, $idioma) {
    return is_array($texto) ? ($texto[$idioma] ?? $texto['es']) : $texto;
}
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="<?= e($idioma) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(t('guias_titulo')) ?> · 1–450</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/layout.css">
<link rel="stylesheet" href="css/guias.css">
<style>
    .user-fixed-box {
        position: fixed; top: 15px; right: 20px; z-index: 9999;
        display: flex; align-items: center; gap: 10px;
        background: rgba(8, 18, 40, 0.85); backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(240,201,74,0.3); border-radius: 30px;
        padding: 5px 6px 5px 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    }
    .user-fixed-name { color: #f0c94a; font-weight: 700; font-size: 13px; white-space: nowrap; display: flex; align-items: center; gap: 6px; }
    .user-fixed-name .badge-gm { background: rgba(240,201,74,0.2); border: 1px solid #f0c94a; color: #f0c94a; font-size: 9px; padding: 1px 5px; border-radius: 8px; font-weight: 700; }
    .btn-lang-fixed, .btn-back-fixed, .btn-logout-fixed {
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 50%; font-size: 13px;
        text-decoration: none; transition: all 0.25s ease;
    }
    .btn-lang-fixed { background: rgba(240, 201, 74, 0.15); border: 2px solid #f0c94a; color: #f0c94a; font-size: 11px; font-weight: 700; }
    .btn-lang-fixed:hover { background: #f0c94a; color: #1a1200; transform: scale(1.1); box-shadow: 0 0 15px rgba(240,201,74,0.6); }
    .btn-back-fixed { background: rgba(240, 201, 74, 0.15); border: 2px solid #f0c94a; color: #f0c94a; }
    .btn-back-fixed:hover { background: #f0c94a; color: #1a1200; transform: scale(1.1); box-shadow: 0 0 15px rgba(240,201,74,0.6); }
    .btn-logout-fixed { background: rgba(220, 53, 69, 0.2); border: 2px solid #dc3545; color: #ff9aa4; }
    .btn-logout-fixed:hover { background: #dc3545; color: #fff; transform: scale(1.1); box-shadow: 0 0 15px rgba(220,53,69,0.6); }

    .guias-layout {
        display: flex;
        gap: 20px;
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
        align-items: flex-start;
    }
    .guias-content { flex: 1; min-width: 0; }
    .guias-nav-vertical {
        flex: 0 0 220px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        position: sticky;
        top: 80px;
        align-self: flex-start;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
        padding: 10px;
        background: rgba(8, 18, 40, 0.85);
        border: 1px solid rgba(240, 201, 74, 0.3);
        border-radius: 12px;
        order: 2;
    }
    .guias-nav-vertical a {
        display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        border-radius: 8px; text-decoration: none; color: #c9d8f0;
        font-size: 13px; font-weight: 600; border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .guias-nav-vertical a:hover { background: rgba(240, 201, 74, 0.1); border-color: rgba(240, 201, 74, 0.3); color: #f0c94a; }
    .guias-nav-vertical a.active { background: linear-gradient(135deg, rgba(240,201,74,0.25), rgba(212,175,55,0.15)); border-color: #f0c94a; color: #f0c94a; }
    .guias-nav-vertical a i { color: var(--c, #f0c94a); font-size: 14px; width: 18px; text-align: center; }

    @media (max-width: 900px) {
        .guias-layout { flex-direction: column; }
        .guias-nav-vertical {
            flex: 0 0 auto; position: static; flex-direction: row;
            overflow-x: auto; overflow-y: hidden; max-height: none;
            white-space: nowrap; order: 0; width: 100%;
        }
        .guias-nav-vertical a { flex: 0 0 auto; }
    }
</style>
</head>
<body>

<?php if (!empty($_SESSION['web_logueado'])): ?>
    <div class="user-fixed-box">
        <span class="user-fixed-name">
            <i class="fa-solid fa-user" style="color:#9db4d8;"></i>
            <?= e($_SESSION['web_username'] ?? '') ?>
            <?php if (!empty($_SESSION['es_admin'])): ?><span class="badge-gm">GM</span><?php endif; ?>
        </span>
        <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= $idioma === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
        <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= $idioma === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
        <a href="index.php" class="btn-back-fixed" title="<?= e(t('back')) ?>"><i class="fa-solid fa-arrow-left"></i></a>
        <a href="index.php?logout=1" class="btn-logout-fixed" title="<?= e(t('logout')) ?>"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
<?php else: ?>
    <div class="user-fixed-box">
        <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= $idioma === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
        <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= $idioma === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
        <a href="index.php" class="btn-back-fixed" title="<?= e(t('back')) ?>"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
<?php endif; ?>

<div class="topbar">
    <nav class="nav nav-vertical">
        <div class="brand-group">
            <a href="index.php" class="brand"><?= htmlspecialchars(SERVER_NAME) ?></a>
        </div>
        <div class="menu menu-vertical">
            <?php if (!empty($_SESSION['web_logueado'])): ?>
                <?php if (!empty($_SESSION['es_admin'])): ?>
                    <a href="admin/index.php" class="btn-nav btn-gm-priority"><span class="ico">👑</span> <?= t('gm_panel') ?></a>
                <?php endif; ?>
                <a href="cuenta.php" class="btn-nav"><span class="ico">👤</span> <?= t('my_account') ?></a>
                <a href="tienda.php" class="btn-nav"><span class="ico">🛒</span> <?= t('shop') ?></a>
                <a href="noticias.php" class="btn-nav"><span class="ico">📰</span> <?= t('news_titulo') ?></a>
                <a href="guias.php" class="btn-nav primary"><span class="ico">📖</span> <?= t('guias_titulo') ?></a>
                <a href="reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
            <?php else: ?>
                <a href="reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<header class="header">
  <h1><?= e(t('guias_titulo')) ?></h1>
  <div class="ornament">◆</div>
  <p><?= e(t('guias_subtitulo')) ?></p>
</header>

<div class="guias-layout">
  <main class="guias-content content">
    <?php foreach ($guias as $g): ?>
      <section class="section" id="p-<?= e($g['slug']) ?>" style="--c:<?= e($g['color']) ?>">
        <h2 class="section-title">
          <i class="fa-solid <?= e($g['icono']) ?>"></i><?= e(g($g['nombre'], $idioma)) ?>
          <span class="max">1 — 450</span>
        </h2>
        <ul class="levels">
          <?php foreach ($g['niveles'] as $n): ?>
            <li>
              <span class="range"><?= e($n[0]) ?></span>
              <div class="info">
                <span class="zone"><?= e(g($n[1], $idioma)) ?></span>
                <span class="note"><?= e(g($n[2], $idioma)) ?></span>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
  </main>

  <nav class="guias-nav-vertical" id="nav">
    <?php foreach ($guias as $g): ?>
      <a href="#p-<?= e($g['slug']) ?>" style="--c:<?= e($g['color']) ?>">
        <i class="fa-solid <?= e($g['icono']) ?>"></i><?= e(g($g['nombre'], $idioma)) ?>
      </a>
    <?php endforeach; ?>
  </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.guias-nav-vertical a');
    const sections = [...links].map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);

    const io = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                links.forEach(a => a.classList.remove('active'));
                const activeLink = document.querySelector('.guias-nav-vertical a[href="#' + entry.target.id + '"]');
                if (activeLink) activeLink.classList.add('active');
            }
        });
    }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });

    sections.forEach(s => io.observe(s));
});
</script>
</body>
</html>