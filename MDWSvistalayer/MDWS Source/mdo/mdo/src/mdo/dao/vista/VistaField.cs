using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaField
    {
        int pos;
        string vistaName;
        string vistaNum;
        string vistaNode;
        string vistaPiece;
        string vistaValue;
        string mdoName;
        string mdoType;
        VistaFieldMapping mapping;

        public bool IsMultiple { get; set; }
        public VistaFile Multiple { get; set; }
        public bool IsWordProc { get; set; }
        public bool IsPointer { get; set; }
        public VistaFile PointsTo { get; set; }
        public VistaFile File { get; set; }
        public string Transform { get; set; }
        public string Type { get; set; }

        public Dictionary<String, String> Externals;

        public VistaField() { }

        public int Pos
        {
            get { return pos; }
            set { pos = value; }
        }

        public string VistaName
        {
            get { return vistaName; }
            set { vistaName = value; }
        }

        public string VistaNumber
        {
            get { return vistaNum; }
            set { vistaNum = value; }
        }

        public string VistaNode
        {
            get { return vistaNode; }
            set { vistaNode = value; }
        }

        public string VistaPiece
        {
            get { return vistaPiece; }
            set { vistaPiece = value; }
        }

        public string VistaValue
        {
            get { return vistaValue; }
            set { vistaValue = value; }
        }

        public string MdoName
        {
            get { return mdoName; }
            set { mdoName = value; }
        }

        public string MdoType
        {
            get { return mdoType; }
            set { mdoType = value; }
        }

        public VistaFieldMapping Mapping
        {
            get { return mapping; }
            set { mapping = value; }
        }
    }
}
