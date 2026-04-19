<template>
    <div class="footer mb-5">
        <marquee :direction="currentLocale === 'ar' ? 'right' : 'left'">
            <div class="scroller" :class="{'scroller__inner-ltr': currentLocale === 'ar'}">
                <ul class="scroller scroller__inner">
                    <li v-for="(message, index) in messages" :key="message.id"
                        :class="['message', `message-${message.type}`]" behavior="tag-list" direction="left"
                        v-html="currentLocale === 'ar' ? message.body_ar : message.body_en">
                    </li>
                </ul>
            </div>
        </marquee>
    </div>
</template>


<script>
import axios from 'axios';

export default {
    data() {
        return {
            messages: [],
            currentLocale: document.documentElement.lang || 'en', // Default to 'en'
        };
    },
    mounted() {
        this.fetchMessages();
    },
    updated() {

    },
    methods: {
        async fetchMessages() {
            try {
                const response = await axios.get('/api/messages'); // Ensure your API endpoint is correct
                this.messages = response.data;
            } catch (error) {
                console.error('Error fetching messages:', error);
            }
        },

    },
};
</script>

<style scoped>
.footer {
    background: #ffffff;
    /* padding: 12px 0; */
    border: 1px solid #e3e3e3;
    border-radius: 5px;
    overflow: hidden;
}
.scroller__inner-ltr {
    direction: ltr;
}
.scroller {
    max-width: 100%;
    display: flex;

}

.scroller__inner {
    padding-top: 3px;
    display: flex;
    /* padding-block: 0.5rem; */
    gap: 1rem;
}

.message-info {
    background-color: #fff3cd;
    color: #856404;
}

.message-danger {
    background-color: #dc3545;
    color: #ffff00;
}

.message-success {
    background-color: #ffffff;
    color: #28a745;
}

.message-plain {
    background-color: #ffffff00;
    color: #000000;
}
.message{
    padding: 5px;
    border: 1px solid #e3e3e300;
    border-radius: 2px;
}
</style>
