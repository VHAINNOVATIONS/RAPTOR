package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.sm.enumeration.ActivityEnum;
import gov.va.med.mhv.sm.enumeration.PerformerTypeEnum;

import java.io.Serializable;

public class LogEntry extends BaseModel implements Serializable{
	
	/**
	 * 
	 */
	private static final long serialVersionUID = -4018227371141743083L;
	private Long userId;
	private ActivityEnum action;
	private boolean status;
	private PerformerTypeEnum performerType;
	private String detail;
	private String activityType;
	private Long messageId;
	private Long tiuCreationId;
	
	public Long getUserId() {
		return userId;
	}
	public void setUserId(Long userId) {
		this.userId = userId;
	}
	public ActivityEnum getAction() {
		return action;
	}
	public void setAction(ActivityEnum action) {
		this.action = action;
	}
	public boolean isStatus() {
		return status;
	}
	public void setStatus(boolean status) {
		this.status = status;
	}
	public PerformerTypeEnum getPerformerType() {
		return performerType;
	}
	public void setPerformerType(PerformerTypeEnum performerType) {
		this.performerType = performerType;
	}
	public String getDetail() {
		return detail;
	}
	public void setDetail(String detail) {
		this.detail = detail;
	}
	public String getActivityType() {
		return activityType;
	}
	public void setActivityType(String activityType) {
		this.activityType = activityType;
	}
	public Long getMessageId() {
		return messageId;
	}
	public void setMessageId(Long messageId) {
		this.messageId = messageId;
	}
	public Long getTiuCreationId() {
		return tiuCreationId;
	}
	public void setTiuCreationId(Long tiuCreationId) {
		this.tiuCreationId = tiuCreationId;
	}
}
