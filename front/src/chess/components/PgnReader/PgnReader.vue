<template>
  <div id="pgnBox">
    <div id="pgnBoxInner">
      <div v-for="(chapter, index) in chapters" :key="index">
        <div class="movesContainer">
          <template v-for="(move, index) in chapter.Moves" :key="index">
            <span v-if="move.teamColor === 'w'" class="moveNumber">{{ move.moveNumber }}.&nbsp;</span>
            <span
              class="moves"
              :data-fen="move.position"
              @click="handleMoveClick(move)"
            >
              {{ move.move }}&nbsp;
            </span>
            <div
              v-for="(comment, index) in renderComments(chapter.Comments, move.moveNumber, move.teamColor)"
              :key="index"
              class="comment"
            >
              {{ comment }}
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';

export default defineComponent({
  name: 'PgnReader',
  props: {
    pgnData: {
      type: Object,
      default: null
    },
    onMoveClick: {
      type: Function,
      required: true
    }
  },
  setup(props) {
    const chapters = computed(() => props.pgnData?.chapter || []);

    const handleMoveClick = (move: any) => {
      props.onMoveClick(move, move.position);
    };

    const renderComments = (comments: any[], moveNumber: number, color: string) => {
      return comments
        .filter(comment => comment.moveNumber === moveNumber && comment.teamColor === color)
        .map(comment => String(comment.text).replace(/\[%cal .*?\]/g, ''));
    };

    return {
      chapters,
      handleMoveClick,
      renderComments
    };
  }
});
</script>

<style scoped>
#pgnBox{
    border-radius: 5vmin;
    background-color: #FBFCFF;
    height: 76vmin;
    width: 50vmin;
    display: flex;
    flex-direction: column;
    padding: 2vmin 2vmin 2vmin 2vmin;
    margin: 2vmin;
    font-family: "Roboto";
}

.moves{
    color: #214a7c;
    font-weight: bold;
    padding-bottom: 1vmin;
    font-size: 1.3750em	;
}

.comments{
    font-size: 1.2250em;
}
</style>