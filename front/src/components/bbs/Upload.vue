<template>
  <div class="p-d-flex p-ai-center p-jc-center p-w-full">
    <div class="bg-gray-200 darg-input-area" @dragover="dragover" @dragleave="dragleave" @drop="drop">
      <input type="file" multiple :id="id" @change="onChange" ref="file" class="file-input" />
      <label :for="id" class="text-gray-600 drag-label p-d-flex p-ai-center p-jc-center" stye="height:100%">
        {{file_msg}}
      </label>
      <div v-for="(file, i) in filelist" :key="i">
        <li class="p-d-flex p-ai-center p-w-full p-px-2 file-list p-jc-between">
          <span>{{file.name.toString()}}</span>
          <input type="file" :name="`bf_file[${i}]`" style="display:none" />
          <Button v-if="file.name" class="p-button-secondary p-button-link p-button-sm" type="button" @click="remove($event, i)" icon="pi pi-times" />
        </li>
      </div>
    </div>
  </div>
  <!-- <div v-for="i in file_count" :key="i">
    <input type="file" name="bf_file[]" />
  </div> -->
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue'
export default defineComponent({
  emits: ['change'],
  props:{
    FileList:Object as any,
    file_count:Number,
  },
  setup (props, context) {
    const id = Math.random().toString(36).substr(2,11); // 
    const filelist = ref<Object[]>(props.FileList);
    const file = ref<HTMLInputElement>();
    if(props.file_count) {
      try {
        for(let i=0;i<props.file_count;i++){
          if(!filelist.value[i]) {
            (filelist.value[i] as any) = {name : ''};
          }
        }
      } catch(e) {
        filelist.value = new Array(props.file_count).fill({name: ''});
      }
    }
    const onChange = (e?:InputEvent) => {
      const files = file.value?.files;
      if(files?.length) {
        let k = 0;
        if(props.file_count) {
          for (let i = 0; i < props.file_count; i++) {
            if(k >= files.length) break;
            const isFile = (filelist.value[i] as any).name !== '' ? filelist.value.find(file => i === (file as any).bf_no) : undefined;
            if(!isFile && (filelist.value[i] as any).name === '') {
              filelist.value[i] = files[k];
              k++;
            }
          }
        }
      } 
      if(filelist.value?.length === 0) file_msg.value = '드래그하거나 클릭하여 업로드하세요.';
      else file_msg.value = '';
      context.emit('change', filelist.value);
    }
    onMounted(()=>{
      if(filelist.value.length > 0) {
        file_msg.value = '';
      }
    })
    const file_msg = ref<string>('드래그하거나 클릭하여 업로드하세요.');
    const remove = (e:Event, i:number) => {
      e.preventDefault();
      e.stopImmediatePropagation();
      e.stopPropagation();
      (filelist.value[i] as any) = {};
      (filelist.value[i] as any).name = '';
      if(filelist.value?.length === 0) file_msg.value = '드래그하거나 클릭하여 업로드하세요.';
      else file_msg.value = '';
      context.emit('change', filelist.value);
    }
    const dragover = (event:DragEvent) => {
      event.preventDefault();
    };
    const dragleave = (event:DragEvent) => {
      event.preventDefault();
    };
    const drop = (event:DragEvent) => {
      event.preventDefault();
      (file.value as HTMLInputElement).files = event.dataTransfer?.files as FileList;
      console.log(event.dataTransfer?.files, (file.value as HTMLInputElement).files);
      onChange();
    }
    return {
      file_msg,
      file,
      filelist,
      id,
      onChange,
      remove,
      dragover,
      dragleave,
      drop,
    }
  },
})
</script>

<style scoped>
.file-input{display:none}
.file-input-label{height:100%;width:100%}
.drag-label{position: absolute;top:0;left:0;width:100%;height:100%;cursor: pointer;}
.drag-label:hover{color: var(--surface-900);}
.darg-input-area{min-height:10rem;width:100%;border:1px dashed;border-color:var(--surface-500);cursor: pointer;position: relative;}
.file-list{border-bottom:1px solid var(--surface-400);border-top:1px solid var(--surface-400);height:2.5rem;margin-top:-1px}
.file-none-list{height:2.5rem;}
</style>