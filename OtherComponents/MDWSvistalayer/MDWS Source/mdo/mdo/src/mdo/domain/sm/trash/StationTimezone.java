package gov.va.med.mhv.sm.model;

import java.io.Serializable;

/**
 * 
 * Timezones  can be obtained from the jvm by running 
 * TimeZone.getAvailableIDs();
 * 
 * However a fairly comprehensive list exists at 
 * <a href="http://www.statoids.com/tus.html">http://www.statoids.com/tus.html</a>
 * It might not contain some of the strange ones found in Indiana.  You will have
 * to do a little more research to figure those out.
 * 
 * 
 * @author vhalommccarw
 *
 */
public class StationTimezone extends BaseModel implements Serializable{

	
	/**
	 * 
	 */
	private static final long serialVersionUID = 3138921409948991005L;
	private String stationNo;
	private String timezone;
	
	
	public String getStationNo() {
		return stationNo;
	}
	public void setStationNo(String stationNo) {
		this.stationNo = stationNo;
	}
	public String getTimezone() {
		return timezone;
	}
	public void setTimezone(String timezone) {
		this.timezone = timezone;
	}
	
	
	
	
}
