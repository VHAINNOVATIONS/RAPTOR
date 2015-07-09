using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdws.dto.vista.mgt
{
    [Serializable]
    public class VistaFieldTO : AbstractTO
    {
        public bool isMultiple;
        public bool isPointer;
        public bool isWordProc;
        public string name;
        public string nodePiece;
        public string number;
        public VistaFileTO pointsTo;
        public string transform;
        public string type;
        public string value;

        public VistaFieldTO() { }

        public VistaFieldTO(VistaField mdo)
        {
            if (mdo == null)
            {
                return;
            }

            if (mdo.PointsTo != null)
            {
                this.pointsTo = new VistaFileTO(mdo.PointsTo);
            }
            this.isMultiple = mdo.IsMultiple;
            this.isPointer = mdo.IsPointer;
            this.isWordProc = mdo.IsWordProc;
            this.name = mdo.VistaName;
            this.nodePiece = mdo.VistaNode;
            this.number = mdo.VistaNumber;
            this.transform = mdo.Transform;
            this.type = mdo.Type;
            this.value = mdo.VistaValue;
        }
    }
}