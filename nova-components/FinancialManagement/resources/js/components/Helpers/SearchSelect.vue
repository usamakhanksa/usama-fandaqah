<template>
    <on-click-outside :do="close">
      <div class="search-select" :class="{ 'is-active': isOpen }">
        <button
          ref="button"
          @click="open"
          type="button"
          class="search-select-input"
        >
          <span v-if="value !== null">{{ value }}</span>
          <span v-else class="search-select-placeholder">{{ __('Select Hotel') }}</span>
        </button>
        <div ref="dropdown" v-show="isOpen" class="search-select-dropdown">
          <input
            class="search-select-search"
            v-model="search"
            :placeholder="__('Search with bank IBAN number')"
            ref="search"
            @keydown.esc="close"
            @keydown.up="highlightPrev"
            @keydown.down="highlightNext"
            @keydown.enter.prevent="selectHighlighted"
            @keydown.tab.prevent
          />
          <ul
            ref="options"
            v-show="filteredOptions.length > 0"
            class="search-select-options"
          >
            <li
              class="search-select-option"
              v-for="(option, i) in filteredOptions"
              :key="option"
              @click="select(option)"
              :class="{ 'is-active': i === highlightedIndex }"
            >
              {{ option.name }} - {{ option.bank_iban_number }}
            </li>
          </ul>
          <div v-show="filteredOptions.length === 0" class="search-select-empty">
            {{ __('No results found for') }} "{{ search }}"
          </div>
        </div>
      </div>
    </on-click-outside>
  </template>
  
  <script>
  import OnClickOutside from "./OnClickOutside.vue"
  import Popper from "popper.js"
  
  export default {
    components: {
      OnClickOutside
    },
    props: ["value", "options", "filterFunction"],
    data() {
      return {
        isOpen: false,
        search: "",
        highlightedIndex: 0
      }
    },
    beforeDestroy() {
      this.popper.destroy()
    },
    computed: {
      filteredOptions() {
        return this.filterFunction(this.search, this.options)
      }
    },
    methods: {
      open() {
        if (this.isOpen) {
          return
        }
        this.isOpen = true
        this.$nextTick(() => {
          this.setupPopper()
          this.$refs.search.focus()
          this.scrollToHighlighted()
        })
      },
      setupPopper() {
        if (this.popper === undefined) {
          this.popper = new Popper(this.$refs.button, this.$refs.dropdown, {
            placement: "bottom"
          })
        } else {
          this.popper.scheduleUpdate()
        }
      },
      close() {
        if (!this.isOpen) {
          return
        }
        this.isOpen = false
        this.$refs.button.focus()
      },
      select(option) {
        this.$emit("input", option.name)
        this.search = ""
        this.highlightedIndex = 0
        this.close()
      },
      selectHighlighted() {
        this.select(this.filteredOptions[this.highlightedIndex])
      },
      scrollToHighlighted() {
        this.$refs.options.children[this.highlightedIndex].scrollIntoView({
          block: "nearest"
        })
      },
      highlight(index) {
        this.highlightedIndex = index
  
        if (this.highlightedIndex < 0) {
          this.highlightedIndex = this.filteredOptions.length - 1
        }
  
        if (this.highlightedIndex > this.filteredOptions.length - 1) {
          this.highlightedIndex = 0
        }
  
        this.scrollToHighlighted()
      },
      highlightPrev() {
        this.highlight(this.highlightedIndex - 1)
      },
      highlightNext() {
        this.highlight(this.highlightedIndex + 1)
      }
    }
  }
  </script>

<style lang="css" scoped>
.search-select {
  position: relative;
}
.search-select-input {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  text-align: right;
  display: block;
  width: 100%;
  border: 1px solid #ddd !important;
  padding: 0.5rem 0.75rem;
  background-color: #fafafa;
  border-radius: 0.25rem;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.search-select-input:focus {
  outline: 0;
  -webkit-box-shadow: 0 0 0 3px rgba(52, 144, 220, 0.5);
  box-shadow: 0 0 0 3px rgba(52, 144, 220, 0.5);
}
.search-select-placeholder {
  color: #8795a1;
}
.search-select.is-active .search-select-input {
  -webkit-box-shadow: 0 0 0 3px rgba(52, 144, 220, 0.5);
  box-shadow: 0 0 0 3px rgba(52, 144, 220, 0.5);
}
.search-select-dropdown {
  margin-top: 0.25rem;
  margin-bottom: 0.25rem;
  position: absolute;
  right: 0;
  left: 0;
  background-color: #f1f3f5;
  padding: 0.5rem;
  border-radius: 0.25rem;
  -webkit-box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
  z-index: 50;
}
.search-select-search {
  display: block;
  margin-bottom: 0.5rem;
  width: 100%;
  padding: 0.5rem 0.75rem;
  background-color: #606f7b;
  color: #fff;
  border-radius: 0.25rem;
}
.search-select-search:focus {
  outline: 0;
}
.search-select-options {
  list-style: none;
  padding: 0;
  position: relative;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  max-height: 14rem;
}
.search-select-option {
  padding: 0.5rem 0.75rem;
  font-size: 12px;
  /* color: #fff; */
  cursor: pointer;
  border-radius: 0.25rem;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.search-select-option:hover {
    background-color: #cbcfd3;
}
.search-select-option.is-active,
.search-select-option.is-active:hover {
    background-color: #569eda;
    color: white;
}
.search-select-empty {
  padding: 0.5rem 0.75rem;
  /* color: #b8c2cc; */
}
</style>
  