package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Comparator;

import gov.va.med.mhv.sm.enumeration.SystemFolderEnum;

public class SystemFolder extends Folder implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 1538952511775236462L;



	public SystemFolder(){
		super();
		systemFolder = true;
	}
	
	
	
	public static final Comparator<SystemFolder> SYSTEM_FOLDER_SORTER = new Comparator<SystemFolder>() {
		public int compare(SystemFolder a, SystemFolder b) {
			SystemFolderEnum af = SystemFolderEnum.valueOf(a.getId());
			SystemFolderEnum bf = SystemFolderEnum.valueOf(b.getId());;
			if(af == null || bf == null) return 0;
			return af.getSortOrder().compareTo(bf.getSortOrder());
		}
	};
	
	
}
