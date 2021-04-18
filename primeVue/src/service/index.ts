
import { getAPI, menuData, RdURL } from '@/type';
export const NodeService = {
	async getMenu() {
		const root:any[] = [];
		const menus = await getAPI('/menus');
		menus.data.forEach((menu:menuData, i: number) => {
			const subdata:any = [];
			menu.sub?.forEach((_sub:menuData, k:number) => {
				const sub = {
					"key": `${i}-${k}`,
					"label": _sub.me_name,
					"data": RdURL(_sub.me_link),
					// "icon": "pi pi-fw pi-star",
					"type": "url",
				}
				subdata.push(sub);
			});
			const data = {
				"key": `${i}`,
				"label": menu.me_name,
				"data": RdURL(menu.me_link),
				"type": "url",
				// "icon": "pi pi-fw pi-star",
				"children": subdata,
			};
			root.push(data);
		});
		return root;
	}
}