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


        <div v-if="level === 0 && comment.vertejums && !isEditing" class="review-rating">
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

      <div v-if="isEditing" class="edit-form">
        <v-rating
          v-if="level === 0"
          v-model="editRatingLocal"
          :length="5"
          color="#FFD700"
          active-color="#FFD700"
          density="compact"
          class="mb-2"
        ></v-rating>
        <v-textarea
          v-model="editTextLocal"
          rows="3"
          dense
          outlined
          hide-details
          counter="500"
          class="mb-2"
        ></v-textarea>
        <div class="d-flex">
          <v-btn
            small
            color="#003D3A"
            :loading="submittingEdit"
            :disabled="!editTextLocal || editTextLocal.trim() === ''"
            @click="submitEdit"
            class="mr-2"
          >
            Saglabāt
          </v-btn>
          <v-btn small text @click="$emit('cancel-edit')">
            Atcelt
          </v-btn>
        </div>
      </div>

      <p v-else class="comment-text">{{ comment.komentārs }}</p>

      <div v-if="!readOnly && !isEditing" class="comment-actions">
        <v-btn
          small
          text
          color="#003D3A"
          class="reply-btn"
          @click="$emit('reply', comment.Atsauksmes_ID)"
        >
          <v-icon small left>mdi-reply</v-icon>
          Atbildēt
        </v-btn>

        <template v-if="isOwner">
          <v-btn
            small
            text
            color="#003D3A"
            class="reply-btn"
            @click="$emit('edit', comment.Atsauksmes_ID)"
          >
            <v-icon small left>mdi-pencil</v-icon>
            Rediģēt
          </v-btn>
          <v-btn
            small
            text
            color="error"
            class="reply-btn"
            @click="$emit('delete-comment', comment)"
          >
            <v-icon small left>mdi-delete</v-icon>
            Dzēst
          </v-btn>
        </template>
      </div>


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
          :current-user-id="currentUserId"
          :editing-comment-id="editingCommentId"
          :submitting-edit="submittingEdit"
          @reply="$emit('reply', $event)"
          @submit-reply="(id, text) => $emit('submit-reply', id, text)"
          @cancel-reply="$emit('cancel-reply')"
          @edit="$emit('edit', $event)"
          @submit-edit="$emit('submit-edit', $event)"
          @cancel-edit="$emit('cancel-edit')"
          @delete-comment="$emit('delete-comment', $event)"
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
    readOnly: { type: Boolean, default: false },
    currentUserId: { type: [Number, String], default: null },
    editingCommentId: { type: [Number, null], default: null },
    submittingEdit: { type: Boolean, default: false }
  },
  // emits - kādus notikumus komponents nosūta uz saiti
  emits: ['reply', 'submit-reply', 'cancel-reply', 'edit', 'submit-edit', 'cancel-edit', 'delete-comment'],
  data() {
    return {
      replyTextLocal: '',
      editTextLocal: '',
      editRatingLocal: 0
    };
  },
  computed: {
    isEditing() {
      return this.editingCommentId === this.comment.Atsauksmes_ID;
    },
    isOwner() {
      return this.currentUserId !== null && String(this.comment.Lietotaja_ID) === String(this.currentUserId);
    }
  },
  // watch - novēro izmaiņas props vai data un reaģē uz tām
  watch: {
    replyForm(newVal) {
      if (newVal === this.comment.Atsauksmes_ID) {
        this.replyTextLocal = '';
      }
    },
    editingCommentId(newVal) {
      if (newVal === this.comment.Atsauksmes_ID) {
        this.editTextLocal = this.comment.komentārs || '';
        this.editRatingLocal = this.comment.vertejums || 0;
      }
    }
  },
  methods: {
    submitEdit() {
      this.$emit('submit-edit', {
        id: this.comment.Atsauksmes_ID,
        text: this.editTextLocal,
        rating: this.level === 0 && this.editRatingLocal ? this.editRatingLocal : null
      });
    },
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
