<template>
    <div class="generated-menu">
        <div>
            <generated-menu-item v-for="(menuItem, index) in firstGroupOfItems"
                :key="menuItem.label"
                :index="index"
                :menu-item="menuItem"
                :icon-size="iconsSize"
                :style="{order: -index}"></generated-menu-item>
        </div>
        <div>
            <generated-menu-item v-for="(menuItem, index) in secondGroupOfItems"
                :key="menuItem.label"
                :menu-item="menuItem"
                :icon-size="iconsSize"
                :style="{order: -index}"></generated-menu-item>
        </div>
    </div>
</template>

<script>
  import GeneratedMenuItem from "./generated-menu-item";

  export default {
    components: {
      GeneratedMenuItem
    },
    props: {
      firstGroupOfItems: Array,
      secondGroupOfItems: Array,
      iconsSize: Number | String
    }
  };
</script>

<style lang="scss">
    @import "../styles/colors";

    .generated-menu {
        display: flex;
        flex-wrap: wrap-reverse;
        background-color: $very-light-grey;
        & > div {
            display: inherit;
            flex-direction: row-reverse;
            flex-wrap: wrap-reverse;
            counter-reset: section;
            &:last-child {
                margin-left: auto;
            }
        }
    }
</style>
