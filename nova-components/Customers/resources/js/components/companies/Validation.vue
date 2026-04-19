<template>
    <div class="row_group error-bag">
          <ul>
              <li v-if="errorBag.name">
                  <ul>
                      <li v-for="(nameError,i) in errorBag.name" :key="i">
                          {{__(nameError)}}
                      </li>
                  </ul>
              </li>
              <li v-if="errorBag.phone">
                  <ul>
                      <li v-for="(phoneError,i) in errorBag.phone" :key="i">
                          {{__(phoneError)}}
                      </li>
                  </ul>
              </li>
              <li v-if="errorBag.email">
                  <ul>
                      <li v-for="(emailError,i) in errorBag.email" :key="i">
                          {{__(emailError)}}
                      </li>
                  </ul>
              </li>
          </ul>
    </div>
</template>

<script>
export default {
    name: 'validation',
    props: {
        errorBag: {
            type: Object,
            required: true
        }
    }
}
</script>

<style lang="scss" scoped>
.error-bag {
        margin: 0 0 1rem !important;
        width: 100%;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 0.25rem;
        color: #721c24;
        ul {
            list-style: none;

            li {
                ul {
                    list-style: inside;
                    li {
                        margin: 10px;
                        font-size: 15px;
                    }
                }
            }
        }
    }
</style>>
