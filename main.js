const $ = id => document.getElementById( id );
const textbox = $( 'ids' );
const filterButtons = document.querySelectorAll( '#filters button' );
const selectedIds = new Set();
let isDragging = false;

const exclusiveFilters = {
  costume: null, day: null, distance: null, favorite: null,
  shadow: null, shiny: null, traded: null, tagged: null
};


( async function displayAllPokemon() {
  try {
    // Fetch the JSON file
    const response = await fetch( 'name_to_id.json' );

    // If the file can't be found, log an error and stop
    if ( !response.ok ) {
      throw new Error( `HTTP error! Status: ${response.status}` );
    }

    const pokemons = await response.json();

    // Get the container element from the HTML
    const container = document.getElementById( 'pokemons' );

    // Loop through the pokemon data (e.g., "Pikachu": 25)
    for ( const pokemonName in pokemons ) {
      const pokemonId = pokemons[pokemonName];
      const imageUrl = `images/${pokemonId}.png`;

      // Create the <button> element
      const button = document.createElement( 'button' );
      button.setAttribute( 'data-id', pokemonId );

      // Create the <span> for the ID
      const idSpan = document.createElement( 'span' );
      idSpan.textContent = pokemonId;

      // Create the <img> element
      const img = document.createElement( 'img' );
      img.src = imageUrl;
      img.width = 64;
      img.height = 64;
      img.alt = pokemonName; // Good for accessibility

      // Add the elements inside the button, including the name
      button.appendChild( idSpan );
      button.appendChild( img );
      button.append( pokemonName ); // Adds the name as text

      // Add the completed button to the container
      container.appendChild( button );
    }
  } catch ( error ) {
    console.error( "Could not load Pokémon data:", error );
    // Optionally display an error message to the user
    const container = document.getElementById( 'pokemon-container' );
    container.textContent = 'Failed to load Pokémon. Please try again later.';
  }
} )();

const getFilterGroup = id =>
  ['#', 'costume', 'day', 'distance', 'favorite', 'traded', 'shadow', 'shiny']
    .find( g => id.startsWith( g ) || id === g || id === `!${g}` || ( g === 'day' && id.startsWith( 'day' ) ) ) || null;

const removeFilter = ( filters, target ) => filters.filter( f => f !== target );

const updateTextarea = () => {
  const sorted = [...selectedIds].sort( ( a, b ) => a - b );
  const result = [];
  for ( let i = 0; i < sorted.length; ) {
    let start = sorted[i], end = start;
    while ( sorted[i + 1] === sorted[i] + 1 ) end = sorted[++i];
    result.push( start === end ? `${start}` : `${start}-${end}` );
    i++;
  }
  textbox.value = result.join( ',' );
};

const toggleFilterButton = btn => {
  const id = btn.id;
  let filters = textbox.value.split( '&' ).filter( Boolean );
  const group = getFilterGroup( id );

  if ( group ) {
    const current = exclusiveFilters[group];
    if ( current === id ) {
      filters = removeFilter( filters, id );
      exclusiveFilters[group] = null;
      btn.classList.remove( 'active' );
    } else {
      if ( current ) {
        filters = removeFilter( filters, current );
        $( current )?.classList.remove( 'active' );
      }
      filters.push( id );
      exclusiveFilters[group] = id;
      btn.classList.add( 'active' );
    }
  } else {
    const isActive = filters.includes( id );
    filters = isActive ? removeFilter( filters, id ) : [...filters, id];
    btn.classList.toggle( 'active', !isActive );
  }

  textbox.value = filters.join( '&' );
};

const pokemonActive = ids =>
  pokemons.querySelectorAll( 'button' ).forEach( btn =>
    btn.classList.toggle( 'active', ids.has( btn.dataset.id ) )
  );

const parseIds = text => {
  const ids = new Set();
  text.split( ',' ).forEach( part => {
    const [start, end] = part.trim().split( '-' ).map( Number );
    if ( !isNaN( start ) ) {
      if ( !isNaN( end ) ) for ( let i = start; i <= end; i++ ) ids.add( i.toString() );
      else ids.add( start.toString() );
    }
  } );
  return ids;
};

const msgHandler = text => {
  const output = $( 'output' );
  output.textContent = text;

  /*   setTimeout( () => {
      output.textContent = "";
    }, 10000 ); */
}

const handlePresetButton = ( buttonId, jsonPath, successMessage, appendString ) => {
  const button = $( buttonId );
  button.addEventListener( 'click', async () => {
    textbox.value = "";

    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = 'Loading...';

    try {
      const res = await fetch( jsonPath );
      if ( !res.ok ) throw new Error( 'Network error' );

      const data = await res.json();
      textbox.value = appendString + data;
      pokemonActive( new Set( data.map( String ) ) );

      msgHandler( `✅ ${successMessage}` );
    } catch ( err ) {
      console.error( `Error fetching ${jsonPath}:`, err );
      msgHandler( "❌ Error loading data." );
    } finally {
      button.disabled = false;
      button.textContent = originalText;
    }
  } );
}

// DOM Load
document.addEventListener( 'DOMContentLoaded', () => {
  const pokemons = $( 'pokemons' );

  pokemons.addEventListener( 'mousedown', e => {
    const btn = e.target.closest( 'button[data-id]' );
    if ( !btn ) return;
    isDragging = true;
    const id = parseInt( btn.dataset.id, 10 );
    if ( selectedIds.has( id ) ) {
      selectedIds.delete( id );
      btn.classList.remove( 'active' );
    } else {
      selectedIds.add( id );
      btn.classList.add( 'active' );
    }
    updateTextarea();
    e.preventDefault();
  } );

  pokemons.addEventListener( 'mouseover', e => {
    if ( !isDragging ) return;
    const btn = e.target.closest( 'button[data-id]' );
    if ( !btn ) return;
    const id = parseInt( btn.dataset.id, 10 );
    if ( !selectedIds.has( id ) ) {
      selectedIds.add( id );
      btn.classList.add( 'active' );
      updateTextarea();
    }
  } );

  document.addEventListener( 'mouseup', () => isDragging = false );

  // Presets
  handlePresetButton( 'raidButton', 'presets/raid.json', 'Top 30 raid Pokemons loaded. Selected 3-4 star and above 2000CP.', "3*,4*&2000cp-&" );
  handlePresetButton( 'glButton', 'presets/great_league.json', 'Top 80 Great League Pokemons list loaded.', "0attack&3-4hp&3-4defense&" );
  handlePresetButton( 'ulButton', 'presets/ultra_league.json', 'Top 80 Ultra League Pokemons list loaded.', "0-1attack&3-4hp&3-4defense&" );
  handlePresetButton( 'trashButton', 'presets/trash.json', 'Trash list loaded. Excludes 4 stars and shiny.', "!4*&!shiny&" );

  // Filter Button
  filterButtons.forEach( btn => btn.addEventListener( 'click', () => toggleFilterButton( btn ) ) );

  // Copy Button
  $( 'copyButton' ).addEventListener( 'click', async () => {
    try {
      await navigator.clipboard.writeText( textbox.value );
      alert( 'Text copied to clipboard!' );
    } catch {
      alert( 'Unable to copy text.' );
    }
  } );

  // Clear Button
  $( 'clearButton' ).addEventListener( 'click', () => textbox.value = "" );
} );
