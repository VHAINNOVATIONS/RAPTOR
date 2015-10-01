package gov.va.med.mhv.sm.model;

import java.io.Serializable;

public class ClinicalUserType extends BaseModel implements Serializable{

	
	/**
	 * 
	 */
	private static final long serialVersionUID = 7699579765324771553L;
	private String name;

	
	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	
	
}
