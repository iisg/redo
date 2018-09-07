<template>
    <a class="generated-menu-item"
        :class="isActive() ? 'active' : ''"
        :href="menuItem.url">
        <icon :name="menuItem.iconName"
            :size="iconSize"></icon>
        <span>{{menuItem.label}}</span>
    </a>
</template>

<script>
  import Icon from "./icon";

  export default {
    components: {
      Icon
    },
    props: {
      menuItem: {
        iconName: String,
        label: String,
        url: String,
        highlightOnlyWhenPathnameEqualsURL: Boolean
      },
      iconSize: Number | String
    },
    methods: {
      isActive() {
        const url = this.menuItem.url;
        if (this.menuItem.highlightOnlyWhenPathnameEqualsURL) {
          return window.location.pathname == url;
        }
        return window.location.pathname.startsWith(url);
      }
    }
  };
</script>

<style lang="scss">
    @import "../styles/colors";
    @import "../styles/variables";

    a.generated-menu-item {
        display: flex;
        flex-grow: 1;
        align-items: center;
        justify-content: center;
        padding: $default-gap-size / 2 $default-gap-size;
        text-decoration: none;
        text-align: center;
        color: $blue;
        transition: box-shadow $default-transition-duration;
        &:hover, &:focus, &.active {
            box-shadow: inset 0 #{-$default-gap-size / 4} 0 0 $blue;
        }
        & > * {
            margin: $default-gap-size / 8;
        }
        .icon {
            flex-shrink: 0;
        }
    }
</style>
