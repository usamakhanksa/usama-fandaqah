<template>
    <div class="card mb-5" :dir="locale == 'en' ? 'ltr' : 'rtl'">
        <div class="image-container">
          <img
            src="https://app.fandaqah.com/images/zatca.png"
            alt="Zatca Logo"
          />

        </div>
        <h1>{{ __('Prepare your property and connect through Fandaqah with the Zakat and Income Authority \"ZATCA\"') }}</h1>

      <p>{{ __('Taxable properties must register in the electronic invoicing system \"Fatora\" of the Zakat, Tax and Customs Authority, to be able to submit electronic invoices to the Authority.') }}</p>
      <p>{{ __('For more details, please contact the sales team.') }}</p>
      <div :dir="locale == 'en' ? 'ltr' : 'rtl'">
        <!-- WhatsApp Button Styled Div -->
        <div class="whatsapp-button" @click="goToWhatsApp">
        <!-- WhatsApp Icon (SVG) -->
            <svg xmlns="http://www.w3.org/2000/svg" height="30" width="27.5" viewBox="0 0 448 512"><path fill="#fff" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                <!-- WhatsApp icon path here -->
            </svg>
            <span>{{ __('Chat on WhatsApp') }}</span>
        </div>
    </div>
    </div>
</template>

<script>
    export default {
        name: "zatca-banner",
        data() {
            return {
                locale : Nova.config.local
            }
        },
        methods: {
        goToWhatsApp() {
            window.open('https://wa.me/message/DTHOGL4M2WMVN1', '_blank');
            }
        }

    }
</script>

<style lang="css" scoped>
.card {
background-color: #fff;
border-radius: 10px;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
padding: 20px;
text-align: center;
border: 1px solid #ddd;
}
h1 {
font-size: 25px;
color: #333;
margin-bottom: 20px;
}
.image-container {
display: flex;
justify-content: center;
gap: 20px;
margin-bottom: 20px;
flex-wrap: wrap;
}
.image-container img {
width: 200px;
height: auto;
}

.announcement-paragraph {
    font-size: 18px !important;
    color: #666 !important;
    line-height: 1.6 !important;
}

p {
    font-size: 18px;
    color: #666;
}
.whatsapp-button {
  margin-top: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: #25d366; /* WhatsApp green */
  color: white;
  padding: 5px 10px;
  border-radius: 8px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Light shadow */
  cursor: pointer;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.whatsapp-button:hover {
  background-color: #169b47; /* Darken on hover */
}

.whatsapp-button svg {
  margin-right: 10px; /* Space between icon and text */
}

[dir="rtl"] .whatsapp-button svg {
  margin: 0 0 0 10px; /* Adjust margin for RTL */
}

[dir="ltr"] .whatsapp-button svg {
  margin: 0 10px 0 0; /* Adjust margin for LTR */
}

.whatsapp-button span {
  font-size: 16px;
  font-weight: bold;
}

</style>
