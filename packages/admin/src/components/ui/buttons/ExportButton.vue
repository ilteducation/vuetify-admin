<template>
  <va-action-button
    v-if="hasAction('list')"
    :hide-label="icon"
    :label="$t('va.actions.export')"
    icon="mdi-download"
    :color="color || 'success'"
    text
    @click="onExport"
  ></va-action-button>
</template>

<script>
import Resource from "../../../mixins/resource";
import Button from "../../../mixins/button";
import * as jsonexport from "jsonexport/dist"

/**
 * Action button for export all data from a list iterator, aka VaList.
 * Use current state of VaList, i.e. keep current filters and sorts while removing pagination limitation.
 * Will provoke a download of a CSV file generated on client side thanks to papaparse library.
 */
export default {
  mixins: [Resource, Button],
  props: {
    /**
     * Current state of list, with mainly current sorting.
     */
    options: {
      type: Object,
      default: () => {},
    },
    /**
     * Current filter state of the list.
     */
    filter: {
      type: Object,
      default: () => {},
    },
    cursorPagination: {
      type: Boolean,
      default: false,
    }
  },
  methods: {
    async onExport() {
      /**
       * Generate CSV string from JSON api
       */
      let params = {
        filter: this.filter,
      };

      if (this.options) {
        const { sortBy, sortDesc } = this.options;

        params.sort = sortBy.map((by, index) => {
          return { by, desc: sortDesc[index] };
        });
      }

      let allData = [];
      let hasMoreData = true;
      if (this.cursorPagination) {
        params.pagination = {
          limit: 50
        };
      } else {
        params.pagination = {
          perPage: 50,
          page: 1
        };
      }
      while (hasMoreData) {
        const { data, total, cursor } = await this.$store.dispatch(
          `${this.resource}/getList`,
          params
        )
        allData.push(...data);
        if (this.cursorPagination) {
          hasMoreData = !!cursor;
          params.pagination.cursor = cursor;
        } else {
          hasMoreData = total > allData.length;
          params.pagination.page += 1;
        }
      }

      const csv = await jsonexport(allData);
      /**
       * Magic download
       */
      const fileName = this.resource;
      const blob = new Blob([csv], { type: "text/csv" });

      if (window.navigator && window.navigator.msSaveOrOpenBlob) {
        // Manage IE11+ & Edge
        window.navigator.msSaveOrOpenBlob(blob, `${fileName}.csv`);
        return;
      }

      const fakeLink = document.createElement("a");
      fakeLink.style.display = "none";
      document.body.appendChild(fakeLink);

      fakeLink.setAttribute("href", URL.createObjectURL(blob));
      fakeLink.setAttribute("download", `${fileName}.csv`);
      fakeLink.click();
    },
  },
};
</script>
