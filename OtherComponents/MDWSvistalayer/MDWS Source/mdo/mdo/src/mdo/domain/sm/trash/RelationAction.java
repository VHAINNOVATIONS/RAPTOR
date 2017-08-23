package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.sm.enumeration.RelationActionEnum;

public class RelationAction {

	private TriageRelation triageRelation;
	private RelationActionEnum action;
	
	public TriageRelation getTriageRelation() {
		return triageRelation;
	}
	public void setTriageRelation(TriageRelation triageRelation) {
		this.triageRelation = triageRelation;
	}
	public RelationActionEnum getAction() {
		return action;
	}
	public void setAction(RelationActionEnum action) {
		this.action = action;
	}
	
	
	
}
