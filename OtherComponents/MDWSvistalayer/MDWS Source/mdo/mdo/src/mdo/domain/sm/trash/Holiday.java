package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;

public class Holiday extends BaseModel implements Serializable{
	
	/**
	 * 
	 */
	private static final long serialVersionUID = -2088779988021087108L;
	private Date date;

	public Date getDate() {
		return date;
	}

	public void setDate(Date date) {
		this.date = date;
	}

}
