package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Comparator;

import gov.va.med.mhv.foundation.model.PersistentObject;

public class Facility extends PersistentObject implements Serializable, Comparable<Facility> {

	/**
	 * 
	 */
	private static final long serialVersionUID = 7086597564023194541L;
	private Long id = null;
	private String name;
	private String stationNumber;
	private Long parentId;
	private Long visnId;
	
	public Long getId() {
		return id;
	}
	public void setId(Long id) {
		this.id = id;
	}
	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	public String getStationNumber() {
		return stationNumber;
	}
	public Long getVisnId() {
		return visnId;
	}
	public void setVisnId(Long visnId) {
		this.visnId = visnId;
	}
	public Long getParentId() {
		return parentId;
	}
	public void setParentId(Long parentId) {
		this.parentId = parentId;
	}
	public String getVisn(){
		return visnId.toString().substring(visnId.toString().length() - 2);
	}
	public void setStationNumber(String stationNumber) {
		this.stationNumber = stationNumber;
	}	
	
	public static final Comparator<Facility> FACILITY_BY_NAME_SORTER = new Comparator<Facility>() {
		public int compare(Facility a, Facility b) {
			if(a == null || b == null) return 0;
			return  a.getName().compareTo(b.getName());
		}
	};

	public int compareTo(Facility other) {
		if ( getName().startsWith("VISN")) {
			if ( other.getName().startsWith("VISN")) {
				// Both VISNs
				try {
					Integer myId = new Integer(getName().substring(5));
					Integer otherId = new Integer(other.getName().substring(5));
					return myId.compareTo(otherId);
				} catch (Exception e) {
					e.printStackTrace();
					return getName().compareTo(other.getName());
				}
			} else {
				// I'm a VISN he isn't
				return -1;
			}
		} else {
			if ( other.getName().startsWith("VISN")) {
				// He's a VISN, I'm not
				return 1;
			} else {
				// Both facilitiess
				return getName().compareTo(other.getName());
			}
		}
	} 
}
