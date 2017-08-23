package gov.va.med.mhv.sm.model;

import java.io.Serializable;

public class PatientFacility extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 66888812314511034L;
	private User user;
	private String stationNo;
	private String dfn;
	
	
	public User getUser() {
		return user;
	}
	public void setUser(User user) {
		this.user = user;
	}
	public String getStationNo() {
		return stationNo;
	}
	public void setStationNo(String stationNo) {
		this.stationNo = stationNo;
	}
	public String getDfn() {
		return dfn;
	}
	public void setDfn(String dfn) {
		this.dfn = dfn;
	}
	
	
}
