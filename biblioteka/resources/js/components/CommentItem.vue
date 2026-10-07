<template>
  <div class="comment-wrapper" :class="{ 'is-reply': level > 0 }">
    <div class="comment-content">
      
      <div class="comment-header">
        <div class="reviewer-info">
          <v-avatar :color="getAvatarColor(comment.lietotaja_vards)" :size="level > 0 ? 32 : 40" class="mr-3">
            <v-img v-if="comment.foto" :src="comment.foto" cover></v-img>
            <span v-else :class="level > 0 ? 'reviewer-initial-small' : 'reviewer-initial'">
              {{ getUserInitial(comment.lietotaja_vards) }}
            </span>
          </v-avatar>
          <div>
            <div class="reviewer-name">
              {{ comment.lietotaja_vards }}
             
              <span v-if="comment.vecakais_lietotaja_vards" class="reply-to">
                → {{ comment.vecakais_lietotaja_vards }}
              </span>
            </div>
            <div class="review-date">{{ formatDate(comment.created_at) }}</div>
          </div>
        </div>

       
        <div v-if="level === 0 && comment.vertejums" class="review-rating">
          <v-icon
            v-for="star in 5"
            :key="star"
            :color="star <= comment.vertejums ? '#FFD700' : '#C0C0C0'"
            size="18"
          >
            mdi-star
          </v-icon>
          <span class="rating-value">({{ comment.vertejums }}/5)</span>
        </div>
      </div>

     
      <p class="comment-text">{{ comment.komentārs }}</p>

     
      <v-btn
        v-if="!readOnly"
        small
        text
        color="#003D3A"
        class="reply-btn"
        @click="$emit('reply', comment.Atsauksmes_ID)"
      >
        <v-icon small left>mdi-reply</v-icon>
        Atbildēt
      </v-btn>

      
      <div v-if="!readOnly && replyForm === comment.Atsauksmes_ID" class="reply-form">
        <v-textarea
          v-model="replyTextLocal"
          placeholder="Rakstiet atbildi..."
          rows="2"
          dense
          outlined
          hide-details
          class="mb-2"
        ></v-textarea>
        <div class="d-flex">
          <v-btn
            small
            color="#003D3A"
            :loading="submittingReply"
            @click="$emit('submit-reply', comment.Atsauksmes_ID, replyTextLocal)"
            class="mr-2"
          >
            Nosūtīt
          </v-btn>
          <v-btn small text @click="$emit('cancel-reply')">
            Atcelt
          </v-btn>
        </div>
      </div>

    
      <div v-if="comment.atbildes && comment.atbildes.length > 0" class="replies-container">
        <CommentItem
          v-for="reply in comment.atbildes"
          :key="reply.Atsauksmes_ID"
          :comment="reply"
          :level="level + 1"
          :reply-form="replyForm"
          :submitting-reply="submittingReply"
          :read-only="readOnly"
          @reply="$emit('reply', $event)"
          @submit-reply="(id, text) => $emit('submit-reply', id, text)"
          @cancel-reply="$emit('cancel-reply')"
        />
      </div>
    </div>
  </div>
</template>

<script>
import '../../css/comment-item.css';

export default {
  name: 'CommentItem',
  // props — ko komponents saņem no ārpuses
  props: {
    comment: { type: Object, required: true },
    level: { type: Number, default: 0 },
    replyForm: { type: [Number, null], default: null },
    submittingReply: { type: Boolean, default: false },
    readOnly: { type: Boolean, default: false }
  },
  // emits - kādus notikumus komponents nosūta uz saiti
  emits: ['reply', 'submit-reply', 'cancel-reply'],
  data() {
    return {
      replyTextLocal: ''
    };
  },
  // watch - novēro izmaiņas props vai data un reaģē uz tām
  watch: {
    replyForm(newVal) {
      if (newVal === this.comment.Atsauksmes_ID) {
        this.replyTextLocal = '';
      }
    }
  },
  methods: {
    getUserInitial(name) {
      if (!name) return 'U';
      return name.charAt(0).toUpperCase();
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('lv-LV', options);
    },
    getAvatarColor(name) {
      if (!name) return '#003D3A';
      const colors = ['#003D3A', '#1565C0', '#2E7D32', '#C62828', '#6A1B9A', '#EF6C00'];
      const index = name.charCodeAt(0) % colors.length;
      return colors[index];
    }
  }
};
</script>
