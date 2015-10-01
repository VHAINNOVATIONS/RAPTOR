using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class RelationAction
    {
        private TriageRelation _triageRelation;

        public TriageRelation TriageRelation
        {
            get { return _triageRelation; }
            set { _triageRelation = value; }
        }
        //private RelationActionEnum _action;

        //public RelationActionEnum Action
        //{
        //    get { return _action; }
        //    set { _action = value; }
        //}
    }
}
