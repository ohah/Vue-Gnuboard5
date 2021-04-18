<template>
	<div>
		<div class="bg-white p-border p-py-4">
			<router-link :to="{name : 'Home'}"> <h2 style="font-weight:800;font-size:2rem;" class="p-text-center">GNUBOARD 5</h2> </router-link>
		</div>
    <Tree :value="nodes">
			<template #default="slotProps">
        <b>{{slotProps.node.label}}</b>
			</template>
			<template #url="slotProps">
				<router-link :to="slotProps.node.data">{{slotProps.node.label}}</router-link>
			</template>
		</Tree>
	</div>
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive, ref, toRefs } from 'vue'
import { NodeService, } from '../service'
export default defineComponent({
	setup () {
    // const nodes = NodeService.getTreeNodes();
		const nodes = ref();
		onMounted(async ()=>{
			nodes.value = await NodeService.getMenu();
			console.log(nodes.value);
		})
		return {
      nodes,
		}
	},
	methods : {}
})
</script>