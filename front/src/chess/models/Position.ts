import {row,column} from "../Constants";
export class Position{
    x: number;
    y: number;
    constructor(x: number,y: number){
        this.x=x;
        this.y=y;
    }

    samePosition(destination: Position): boolean{
        return this.x === destination.x && this.y === destination.y;
    }
    clone(): Position{
        return new Position(this.x,this.y);
    }
    toString(): string{        
        return column[this.x] + row[this.y];
    }
}