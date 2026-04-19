<template>
  <div class="autocomplete">
    <div class="autocomplete__box" :class="{'autocomplete__searching' : showResults}">

       <svg class="autocomplete__icon"  v-if="!isLoading" xmlns="http://www.w3.org/2000/svg" version="1.1" width="16" height="16" viewBox="0 0 16 16">
        <g>
            <path stroke="#999" stroke-width="3" stroke-linecap="round" fill="none" d="M11 11l3.5 3.5"></path>
            <circle stroke="#999" stroke-width="2" cx="6.5" cy="6.5" r="5.5" fill="none"></circle>
        </g>
        </svg>

       <svg v-else class="autocomplete__icon animate-spin" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	    viewBox="-18 172 450 450" style="enable-background:new -18 172 450 450;" xml:space="preserve">
        <g fill="#999">
            <path d="M226.7,289c0,10.9-8.8,19.7-19.7,19.7l0,0c-10.9,0-19.7-8.8-19.7-19.7v-96.6c0-10.9,8.8-19.7,19.7-19.7l0,0
                c10.8,0,19.7,8.8,19.7,19.7V289z"/>
            <path d="M226.7,601.6c0,10.9-8.8,19.7-19.7,19.7l0,0c-10.9,0-19.7-8.8-19.7-19.7V505c0-10.9,8.8-19.7,19.7-19.7l0,0
                c10.8,0,19.7,8.8,19.7,19.7V601.6L226.7,601.6z"/>
            <path d="M99,377.3c10.9,0,19.7,8.8,19.7,19.6l0,0c0,10.9-8.8,19.7-19.7,19.7H2.4c-10.9,0-19.7-8.8-19.7-19.7l0,0
                c0-10.9,8.8-19.6,19.7-19.6H99z"/>
            <path d="M411.6,377.3c10.9,0,19.7,8.8,19.7,19.6l0,0c0,10.9-8.8,19.7-19.7,19.7H315c-10.9,0-19.7-8.8-19.7-19.7l0,0
                c0-10.9,8.8-19.6,19.7-19.6H411.6z"/>
            <path d="M144.5,306.7c7.7,7.7,7.7,20.1,0,27.8l0,0c-7.7,7.7-20.2,7.7-27.9,0l-68.3-68.3c-7.7-7.7-7.7-20.1,0-27.9l0,0
                c7.7-7.7,20.1-7.7,27.8,0L144.5,306.7L144.5,306.7z"/>
            <path d="M365.6,527.8c7.7,7.7,7.7,20.2,0,27.9l0,0c-7.7,7.7-20.1,7.7-27.8-0.1l-68.3-68.3c-7.7-7.7-7.7-20.2,0-27.9l0,0
                c7.7-7.7,20.2-7.7,27.8,0L365.6,527.8L365.6,527.8z"/>
            <path d="M116.7,459.5c7.7-7.7,20.2-7.7,27.9,0l0,0c7.7,7.7,7.7,20.2,0,27.9l-68.3,68.3c-7.7,7.7-20.1,7.7-27.8-0.1l0,0
                c-7.7-7.7-7.7-20.1,0-27.8L116.7,459.5z"/>
            <path d="M337.8,238.4c7.7-7.7,20.1-7.7,27.8,0l0,0c7.7,7.7,7.7,20.2,0,27.9l-68.3,68.3c-7.7,7.7-20.2,7.7-27.8,0l0,0
                c-7.7-7.7-7.7-20.2,0-27.8L337.8,238.4L337.8,238.4z"/>
        </g>
        </svg>



      <div class="autocomplete__inputs">
        <input
          v-model="display"
          :placeholder="placeholder"
          :disabled="disableInput"
          :maxlength="maxlength"
          :class="inputClass"
          @click="search"
          @input="search"
          @keydown.enter="enter"
          @keydown.tab="close"
          @keydown.up="up"
          @keydown.down="down"
          @keydown.esc="close"
          @focus="focus"
          @blur="blur"
          type="text"
          autocomplete="off">
        <input :name="name" type="hidden" :value="value">
      </div>

      <!-- clearButtonIcon -->
      <button v-show="!disableInput && !isEmpty && !isLoading && !hasError && from == 'edit-customer'" @click="clear" class="change_user_btn">{{__('Change Customer')}}</button>
      <span   v-show="!disableInput && !isEmpty && !isLoading && !hasError && from == null " class="autocomplete__icon autocomplete--clear" @click="clear">
        <span v-if="clearButtonIcon" :class="clearButtonIcon"></span>
      </span>
    </div>

    <ul v-show="showResults" class="autocomplete__results" :style="listStyle">
      <slot name="results">
        <!-- error -->
        <li v-if="hasError" class="autocomplete__results__item autocomplete__results__item--error">{{ error }}</li>

        <!-- results -->
        <template v-if="!hasError">
          <slot name="firstResult"></slot>
          <li
              v-for="(result, key) in results"
              :key="key"
              @click.prevent="select(result)"
              class="autocomplete__results__item"
              :class="{'autocomplete__selected' : isSelected(key) }"
              >
              <!-- v-html="formatDisplay(result)" -->
              <span class="user">{{result.name}}</span>
              <span class="phone">{{result.phone}}</span>

          </li>
          <slot name="lastResult"></slot>
        </template>

        <!-- no results -->
        <li v-if="noResultMessage" class="autocomplete__results__item autocomplete__no-results">
          <slot name="noResults">No Results.</slot>
        </li>
      </slot>
    </ul>
  </div>
</template>

<script>
import debounce from 'lodash/debounce'
export default {
  props: {
    /**
     * Data source for the results
     *   `String` is a url, typed input will be appended
     *   `Function` received typed input, and must return a string; to be used as a url
     *   `Array` and `Object` (see `results-property`) are used directly
     */

    from: {
      type: String,
      required: false,
      default : null
    },

    /**
     * A delay property. (optional)
     */
    delay: {
      type: [Number],
      required: false,
      default: 200
    },
    source: {
      type: [String, Function, Array, Object],
      required: true
    },
    /**
     * http method
     */
    method: {
      type: String,
      default: 'get'
    },
    /**
     * Input placeholder
     */
    placeholder: {
      default: 'Search'
    },
    /**
     * Preset starting value
     */
    initialValue: {
      type: [String, Number]
    },
    /**
     * Preset starting display value
     */
    initialDisplay: {
      type: String
    },
    /**
     * CSS class for the surrounding input div
     */
    inputClass: {
      type: [String, Object]
    },
    /**
     * To disable the input
     */
    disableInput: {
      type: Boolean
    },
    /**
     * name property of the input holding the selected value
     */
    name: {
      type: String
    },
    /**
     * api - property of results array
     */
    resultsProperty: {
      type: String
    },
    /**
     * Results property used as the value
     */
    resultsValue: {
      type: String,
      default: 'id'
    },
    /**
     * Results property used as the display
     */
    resultsDisplay: {
      type: [String, Function],
      default: 'name'
    },

    /**
     * Callback to format the server data
     */
    resultsFormatter: {
      type: Function
    },

    /**
     * Whether to show the no results message
     */
    showNoResults: {
      type: Boolean,
      default: true
    },

    /**
     * Additional request headers
     */
    requestHeaders: {
      type: Object
    },

    /**
     * Credentials: same-origin, include, *omit
     */
    credentials: {
      type: String
    },

    /**
     * Optional clear button icon class
     */
    clearButtonIcon: {
      type: String
    },

    /**
     * Optional max input length
     */
    maxlength: {
      type: Number
    }
  },
  data () {
    return {
      value: null,
      display: null,
      results: null,
      selectedIndex: null,
      loading: false,
      isFocussed: false,
      error: null,
      selectedId: null,
      selectedDisplay: null,
      eventListener: false,
      lastRequest: null
    }
  },
  computed: {
    showResults () {
      return Array.isArray(this.results) || this.hasError
    },
    noResults () {
      return Array.isArray(this.results) && this.results.length === 0
    },
    noResultMessage () {
      return this.noResults &&
        !this.isLoading &&
        this.isFocussed &&
        !this.hasError &&
        this.showNoResults
    },
    isEmpty () {
      return !this.display
    },
    isLoading () {
      return this.loading === true
    },
    hasError () {
      return this.error !== null
    },
    listStyle () {
      if (this.isLoading) {
        return {
          color: '#ccc'
        }
      }
    }
  },
  methods: {
    /**
     * Search wrapper method that emits an event for the real search with debounce
     */
    search () {
      this.$emit('search', this.delay)
    },

    /**
     * This method is called by the event inside mounted lch for the debounce propouse
     */
    searchEmitted () {
      this.selectedIndex = null
      switch (true) {
        case typeof this.source === 'string':
          // No resource search with no input
          if (!this.display || this.display.length < 1) {
            return
          }

          return this.resourceSearch(this.source + this.display)
        case typeof this.source === 'function':
          // No resource search with no input
          if (!this.display || this.display.length < 1) {
            return
          }
          return this.resourceSearch(this.source(this.display))
        case Array.isArray(this.source):
          return this.arrayLikeSearch()
        default:
          throw new TypeError()
      }
    },

    /**
     * Search query before making http requests
     * @param {String} url
     */
    resourceSearch (url) {
      if (!this.display) {
        this.results = []
        return
      }
      this.loading = true
      this.setEventListener()
      this.request(url)
    },

    /**
     * Make an http request for results
     * @param {String} url
     */
    request (url) {
      if (this.lastRequest) {
        this.lastRequest.abort()
      }

      // eslint-disable-next-line no-undef
      this.lastRequest = new AbortController()
      const signal = this.lastRequest.signal

      let promise = fetch(url, {signal}, {
        method: this.method,
        credentials: this.getCredentials(),
        headers: this.getHeaders()
      })

      return promise
        .then(response => {
          if (response.ok) {
            this.error = null
            this.lastRequest = null
            return response.json()
          }
          throw new Error('Network response was not ok.')
        })
        .then(response => {
          this.results = this.setResults(response)
          this.emitRequestResultEvent()
          this.loading = false
        })
        .catch(error => {
          this.error = error.message
          this.loading = false
          this.lastRequest = null
        })
    },

    /**
     * Set some default headers and apply user supplied headers
     */
    getHeaders () {
      const headers = {
        'Accept': 'application/json, text/plain, */*'
      }
      if (this.requestHeaders) {
        for (var prop in this.requestHeaders) {
          headers[prop] = this.requestHeaders[prop]
        }
      }
      return new Headers(headers)
    },

    /**
     * Set default credentials and apply user supplied value
     */
    getCredentials () {
      let credentials = 'same-origin'
      if (this.credentials) {
        credentials = this.credentials
      }
      return credentials
    },

    /**
     * Set results property from api response
     * @param {Object|Array} response
     * @return {Array}
     */
    setResults (response) {
      if (this.resultsFormatter) {
        return this.resultsFormatter(response)
      }
      if (this.resultsProperty && response[this.resultsProperty]) {
        return response[this.resultsProperty]
      }
      if (Array.isArray(response)) {
        return response
      }
      return []
    },

    /**
     * Emit an event based on the request results
     */
    emitRequestResultEvent () {
      if (this.results.length === 0) {
        this.$emit('noResults', {query: this.display})
      } else {
        this.$emit('results', {results: this.results})
      }
    },

    /**
     * Search in results passed via an array
     */
    arrayLikeSearch () {
      this.setEventListener()
      if (!this.display) {
        this.results = this.source
        this.$emit('results', {results: this.results})
        this.loading = false
        return true
      }
      this.results = this.source.filter((item) => {
        return this.formatDisplay(item).toLowerCase().includes(this.display.toLowerCase())
      })
      this.$emit('results', {results: this.results})
      this.loading = false
    },

    /**
     * Select a result
     * @param {Object}
     */
    select (obj) {
      if (!obj) {
        return
      }

      this.value = (this.resultsValue && obj[this.resultsValue]) ? obj[this.resultsValue] : obj.id
      this.display = this.formatDisplay(obj)
      this.selectedDisplay = this.display
      this.$emit('selected', {
        value: this.value,
        display: this.display,
        selectedObject: obj
      })
      this.$emit('input', this.value)
      this.close()
    },

    /**
     * @param  {Object} obj
     * @return {String}
     */
    formatDisplay (obj) {
      switch (typeof this.resultsDisplay) {
        case 'function':
          return this.resultsDisplay(obj)
        case 'string':
          if (!obj[this.resultsDisplay]) {
            throw new Error(`"${this.resultsDisplay}" property expected on result but is not defined.`)
          }
          return obj[this.resultsDisplay]
        default:
          throw new TypeError()
      }
    },

    /**
     * Register the component as focussed
     */
    focus () {
      this.isFocussed = true
    },

    /**
     * Remove the focussed value
     */
    blur () {
      this.isFocussed = false
    },

    /**
     * Is this item selected?
     * @param {Object}
     * @return {Boolean}
     */
    isSelected (key) {
      return key === this.selectedIndex
    },

    /**
     * Focus on the previous results item
     */
    up () {
      if (this.selectedIndex === null) {
        this.selectedIndex = this.results.length - 1
        return
      }
      this.selectedIndex = (this.selectedIndex === 0) ? this.results.length - 1 : this.selectedIndex - 1
    },

    /**
     * Focus on the next results item
     */
    down () {
      if (this.selectedIndex === null) {
        this.selectedIndex = 0
        return
      }
      this.selectedIndex = (this.selectedIndex === this.results.length - 1) ? 0 : this.selectedIndex + 1
    },

    /**
     * Select an item via the keyboard
     */
    enter () {
      if (this.selectedIndex === null) {
        this.$emit('nothingSelected', this.display)
        return
      }
      this.select(this.results[this.selectedIndex])
      this.$emit('enter', this.display)
    },

    /**
     * Clear all values, results and errors
     */
    clear () {
      this.display = null
      this.value = null
      this.results = null
      this.error = null
      this.$emit('clear')
    },

    /**
     * Close the results list. If nothing was selected clear the search
     */
    close () {
      if (!this.value || !this.selectedDisplay) {
        this.clear()
      }
      if (this.selectedDisplay !== this.display && this.value) {
        this.display = this.selectedDisplay
      }

      this.results = null
      this.error = null
      this.removeEventListener()
      this.$emit('close')
    },

    /**
     * Add event listener for clicks outside the results
     */
    setEventListener () {
      if (this.eventListener) {
        return false
      }
      this.eventListener = true
      document.addEventListener('click', this.clickOutsideListener, true)
      return true
    },

    /**
     * Remove the click event listener
     */
    removeEventListener () {
      this.eventListener = false
      document.removeEventListener('click', this.clickOutsideListener, true)
    },

    /**
     * Method invoked by the event listener
     */
    clickOutsideListener (event) {
      if (this.$el && !this.$el.contains(event.target)) {
        this.close()
      }
    }
  },
  mounted () {
    this.$on('search', debounce(this.searchEmitted, this.delay))

    Nova.$on('defaults_to_auto_complete_when_clear' , (obj) => {
        this.value = obj.customer_id;
        this.display = null
        this.selectedDisplay = null;
        this.initialDisplay = null
    })

    Nova.$on('defaults_to_auto_complete_when_open' , (obj) => {
        this.value = obj.customer_id;
        this.display = obj.display
        this.selectedDisplay = obj.display;
        this.initialDisplay = obj.display;
    })

    this.value = this.initialValue
    this.display = this.initialDisplay
    this.selectedDisplay = this.initialDisplay
  }
}
</script>

<style lang="css" scoped>

.change_user_btn{

}

.clear_me_btn {
background: #4099de;
 border-radius: 5px;
 border: 1px solid #4099de;
 height: 35px;
 line-height: 35px;
 font-size: 15px;
 padding: 0 15px;
 color: #fff;
 margin: 0 auto 10px;
 -webkit-transition: all 0.2s ease-in-out;
 -moz-transition: all 0.2s ease-in-out;
 -o-transition: all 0.2s ease-in-out;
 transition: all 0.2s ease-in-out;

}

/* 1 */
button.change_user_btn {
    background-color: #4099de;
    height: 38px;
    margin: 0 0 0 -10px;
    border-radius: 4px 0 0 4px;
    padding: 0 10px;
    color: #fff;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
}


/* 2 */
/* button.change_user_btn {
    background-color: #4099de;
    height: 32px;
    border-radius: 4px;
    padding: 0 10px;
    color: #fff;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 0 0 -6px;
} */

button.change_user_btn:hover {
	 background: #0071c9;
	 border-color: #0071c9;
}
.autocomplete {
  position: relative;
  width: 100%;
}
.autocomplete * {
  box-sizing: border-box;
}
.autocomplete__box {
  display: flex;
  align-items: center;
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 3px;
  padding: 0 5px;
}
.autocomplete__searching {
  border-radius: 3px 3px 0 0;
}
.autocomplete__inputs {
  flex-grow: 1;
  padding: 0 5px;
}
.autocomplete__inputs input {
  width: 100%;
  border: 0;
}
.autocomplete__inputs input:focus {
  outline: none;
}
.autocomplete--clear {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='144 -154 1100 1100' style='enable-background:new 144 -154 1100 1100;' xml:space='preserve'%3E%3Cg fill='%23999'%3E%3Cpath d='M1158.7,53.6L816.3,396l342.4,342.4l0,0c15.7,15.7,25.3,37.3,25.3,61.1c0,47.7-38.7,86.5-86.5,86.5 c-23.9,0-45.5-9.7-61.1-25.3l0,0L694,518.3L351.6,860.7l0,0C336,876.3,314.3,886,290.5,886c-47.8,0-86.5-38.7-86.5-86.5 c0-23.9,9.7-45.5,25.3-61.1l0,0L571.7,396L229.3,53.6l0,0C213.7,38,204,16.3,204-7.5c0-47.8,38.7-86.5,86.5-86.5 c23.9,0,45.5,9.7,61.1,25.3l0,0L694,273.7l342.4-342.4l0,0c15.6-15.6,37.3-25.3,61.1-25.3c47.8,0,86.5,38.7,86.5,86.5 C1184,16.3,1174.3,38,1158.7,53.6L1158.7,53.6z'/%3E%3C/g%3E%3C/svg%3E%0A");
}
.autocomplete__results {
  margin: 0;
  padding: 0;
  list-style-type: none;
  z-index: 1000;
  position: absolute;
  max-height: 400px;
  overflow-y: auto;
  background: #fff;
  width: 100%;
  border: 1px solid #ccc;
  border-top: 0;
  color: #000;
}
.autocomplete__results__item--error {
  color: #f00;
}
.autocomplete__results__item {
    cursor: pointer;
}
.autocomplete__results__item:hover {
  background: rgba(0,180,255,0.075);
}
.autocomplete__results__item.autocomplete__selected {
  background: rgba(0,180,255,0.15);
}
.autocomplete__icon {
  height: 14px;
  width: 14px;

}
.animate-spin {
  animation: spin 2s infinite linear;
}
@-moz-keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
@-webkit-keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
@-o-keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

</style>
