<template>
  <div>
    <input
      :disabled="disabled"
      :class="{'!cursor-not-allowed': disabled}"
      :value="value"
      ref="datePicker"
      type="text"
      :placeholder="__('Date From / To')"
    >
  </div>
</template>
<script>
import flatpickr from 'flatpickr'
import '../../airbnb-modified.css'

export default {
  name : 'date-range-filter',
  props: {
      resourceName: {
          type: String,
          required: true,
      },
      filterKey: {
          type: String,
          required: true,
      },

  },

  data: () => ({ flatpickr: null }),

  computed: {
    placeholder() {
      return this.filter.placeholder || this.__('Pick a date range')
    },

    value() {
      if (typeof this.filter.currentValue === 'object' && this.filter.currentValue.length >= 2){
          return moment(this.filter.currentValue[0]).format(this.toMomentsFormat(this.dateFormat))+
              ' '+this.separator+' '+
              moment(this.filter.currentValue[1]).format(this.toMomentsFormat(this.dateFormat));
      }
        return this.filter.currentValue || null
    },
    filter() {
        return this.$store.getters[`${this.resourceName}/getFilter`](this.filterKey)
    },
    disabled() {
      return this.filter.disabled
    },
    separator() {
        return this.filter.separator || '-'
    },

    dateFormat() {
      return this.filter.dateFormat || 'Y-m-d'
    },

    twelveHourTime() {
      return this.filter.twelveHourTime
    },

    enableTime() {
      return this.filter.enableTime
    },

    enableSeconds() {
      return this.filter.enableSeconds
    }
  },

  mounted() {
    const self = this
    this.$nextTick(() => {
      this.flatpickr = flatpickr(this.$refs.datePicker, {
        enableTime: this.enableTime,
        enableSeconds: this.enableSeconds,
        onClose: this.handleChange,
        dateFormat: this.dateFormat,
        allowInput: true,
        // static: true,
        mode: 'range',
        time_24hr: !this.twelveHourTime,
        onReady() {
          self.$refs.datePicker.parentNode.classList.add('date-filter')
        },
          locale: {
              rangeSeparator: ` ${this.separator} `
          }
      })
      const wrapper = document.querySelector('.dropdown-menu div')
      wrapper.classList.remove('overflow-hidden')
    })
  },

  methods: {
    handleChange(value) {
        value = value.map(value => {
            return moment(value).format(this.toMomentsFormat(this.dateFormat))
        });
        this.$store.commit(`${this.resourceName}/updateFilterState`, {
            filterClass: this.filterKey,
            value,
        });
        this.$emit('change')
    },
    toMomentsFormat(format){
        var res=format;
        res = res.replace('Y', 'YYYY');
        res = res.replace('m', 'MM');
        res = res.replace('d', 'DD');
        return res;
    }
  }
}
</script>
